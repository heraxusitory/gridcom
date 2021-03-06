<?php


namespace App\Services\ConsignmentRegisters;


use App\Events\NewStack;
use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Models\User;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CreateConsignmentRegisterService implements IService
{
    public function __construct(private $payload)
    {
    }

    public function run()
    {
        $data = $this->payload;
        /** @var User $user */
        $user = auth('webapi')->user();

        switch ($data['action']) {
            case ConsignmentRegister::ACTION_DRAFT:
                $customer_status = ConsignmentRegister::CUSTOMER_STATUS_DRAFT;
                $contr_agent_status = ConsignmentRegister::CONTRACTOR_STATUS_DRAFT;
                break;
            case ConsignmentRegister::ACTION_APPROVE:
                $customer_status = ConsignmentRegister::CUSTOMER_STATUS_UNDER_CONSIDERATION;
                $contr_agent_status = $user->isProvider() ? ConsignmentRegister::CONTRACTOR_STATUS_UNDER_CONSIDERATION : ConsignmentRegister::CONTRACTOR_STATUS_SELF_PURCHASE;
                break;
            default:
                throw new BadRequestException('Action is required', 400);
        }

        return DB::transaction(function () use ($data, $customer_status, $contr_agent_status) {
            /** @var ConsignmentRegister $consignment_register */
            $consignment_register = ConsignmentRegister::query()->create([
                'uuid' => Str::uuid(),
                'date' => Carbon::today()->format('Y-m-d'),
                'customer_status' => $customer_status,
                'contr_agent_status' => $contr_agent_status,
                'organization_id' => $data['organization_id'],
                'contractor_contr_agent_id' => $data['contractor_contr_agent_id'],
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
                'customer_object_id' => $data['customer_object_id'],
                'customer_sub_object_id' => $data['customer_sub_object_id'] ?? null,
                'work_agreement_id' => $data['work_agreement_id'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
            ]);

            foreach ($data['positions'] as $position) {
                $consignment_register->positions()->create([
                    'position_id' => Str::uuid(),
                    'consignment_id' => $position['consignment_id'],
                    'nomenclature_id' => $position['nomenclature_id'],
                    'price_without_vat' => $position['price_without_vat'],
                    'amount_without_vat' => $position['amount_without_vat'],
                    'count' => $position['count'],
                    'vat_rate' => $position['vat_rate'],
                    'amount_with_vat' => $position['amount_with_vat'],
                ]);
            }

            event(new NewStack($consignment_register,
                    (new ProviderSyncStack())->setProvider($consignment_register->provider),
                    (new ContractorSyncStack())->setContractor($consignment_register->contractor))
            );

            if (in_array($consignment_register->contr_agent_status, [ConsignmentRegister::CONTRACTOR_STATUS_SELF_PURCHASE, ConsignmentRegister::CONTRACTOR_STATUS_AGREED])) {
                event(new NewStack($consignment_register,
                        (new MTOSyncStack()))
                );
            }

            return $consignment_register;
        });
    }
}
