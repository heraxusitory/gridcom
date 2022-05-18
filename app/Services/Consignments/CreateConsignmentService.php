<?php


namespace App\Services\Consignments;


use App\Events\NewStack;
use App\Models\Consignments\Consignment;
use App\Models\References\Nomenclature;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CreateConsignmentService implements IService
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct(private $payload)
    {
        $this->user = auth('webapi')->user();
    }

    public function run()
    {
        $data = $this->payload;

        $is_approved = match ($data['action']) {
            Consignment::ACTION_DRAFT() => false,
            Consignment::ACTION_APPROVE() => true,
            default => throw new BadRequestException('Action is required', 400),
        };

        return DB::transaction(function () use ($data, $is_approved) {
            /** @var Consignment $consignment */
            $consignment = Consignment::query()->create([
                'uuid' => Str::uuid(),
                'date' => Carbon::today()->format('Y-m-d'),
                'organization_id' => $data['organization_id'],
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
                'provider_contract_id' => $data['provider_contract_id'],
                'contractor_contr_agent_id' => $data['contractor_contr_agent_id'],
                'work_agreement_id' => $data['work_agreement_id'],
                'customer_object_id' => $data['customer_object_id'],
                'customer_sub_object_id' => $data['customer_sub_object_id'] ?? null,
                'is_approved' => $is_approved,
//                'order_id' => $data['order_id'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
            ]);

            foreach ($data['positions'] ?? [] as $position) {
                $nomenclature = Nomenclature::query()->findOrFail($position['nomenclature_id']);
                $amount_without_vat = round($nomenclature->price * $position['count'], 2);
                $amount_with_vat = round($amount_without_vat * $position['vat_rate'], 2);
                $consignment->positions()->create([
                    'position_id' => Str::uuid(),
                    'order_id' => $position['order_id'],
                    'nomenclature_id' => $position['nomenclature_id'],
                    'count' => $position['count'],
                    'price_without_vat' => $nomenclature->price,
                    'amount_without_vat' => $amount_without_vat,
                    'vat_rate' => $position['vat_rate'],
                    'amount_with_vat' => $amount_with_vat,
                    'country' => $position['country'],
                    'cargo_custom_declaration' => $position['cargo_custom_declaration'],
                    'declaration' => $position['declaration'],
                ]);
            }

            if ($this->user->isContractor())
                event(new NewStack($consignment,
                    (new ProviderSyncStack())->setProvider($this->user->contr_agent)),
                    (new MTOSyncStack())
                );
            if ($this->user->isProvider())
                event(new NewStack($consignment,
                    (new ContractorSyncStack())->setContractor($this->user->contr_agent)),
                    (new MTOSyncStack())
                );
            return $consignment;
        });

    }
}
