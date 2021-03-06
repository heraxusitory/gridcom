<?php


namespace App\Services\Consignments;


use App\Events\NewStack;
use App\Models\Consignments\Consignment;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\References\Nomenclature;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateConsignmentService implements IService
{
    private $user;

    public function __construct(private $payload, private Consignment $consignment)
    {
        $this->user = auth('webapi')->user();
    }

    public function run()
    {
        $data = $this->payload;

        $this->consignment->is_approved = match ($data['action']) {
            Consignment::ACTION_DRAFT() => false,
            Consignment::ACTION_APPROVE() => true,
            default => throw new BadRequestException('Action is required', 400),
        };

        return DB::transaction(function () use ($data) {
            $this->consignment->update([
                'date' => Carbon::today()->format('Y-m-d'),
//                'order_id' => $data['order_id'],
                'organization_id' => $data['organization_id'],
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
                'provider_contract_id' => $data['provider_contract_id'],
                'contractor_contr_agent_id' => $data['contractor_contr_agent_id'],
                'work_agreement_id' => $data['work_agreement_id'],
                'customer_object_id' => $data['customer_object_id'],
                'customer_sub_object_id' => $data['customer_sub_object_id'] ?? null,
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
            ]);

            $position_ids = [];
            foreach ($data['positions'] ?? [] as $position) {
                $nomenclature = Nomenclature::query()->findOrFail($position['nomenclature_id']);
                $amount_without_vat = round($position['price_without_vat'] * $position['count'], 2);
                $amount_with_vat = round($amount_without_vat * $position['vat_rate'], 2);
                $position = $this->consignment->positions()->updateOrCreate([
                    'position_id' => $position['position_id'] ?? null
                ], [
                    'position_id' => $position['position_id'] ?? Str::uuid(),
                    'order_id' => $position['order_id'],
                    'nomenclature_id' => $position['nomenclature_id'],
                    'count' => $position['count'],
                    'price_without_vat' => $position['price_without_vat'],
                    'amount_without_vat' => $amount_without_vat,
                    'vat_rate' => $position['vat_rate'],
                    'amount_with_vat' => $amount_with_vat,
                    'country' => $position['country'],
                    'cargo_custom_declaration' => $position['cargo_custom_declaration'] ?? null,
                    'declaration' => $position['declaration'] ?? null,
                ]);
                $position_ids[] = $position->id;
            }

            $this->consignment->positions()->whereNotIn('id', $position_ids)->delete();

            event(new NewStack($this->consignment,
                    (new ProviderSyncStack())->setProvider($this->consignment->provider),
                    (new ContractorSyncStack())->setContractor($this->consignment->contractor),
                    (new MTOSyncStack()))
            );
            foreach ($this->consignment->positions as $position) {
                event(new NewStack($position->order,
                        (new ProviderSyncStack())->setProvider($this->consignment->provider),
                        (new ContractorSyncStack())->setContractor($this->consignment->contractor),
                        new MTOSyncStack())
                );
            }

            return $this->consignment;
        });
    }
}
