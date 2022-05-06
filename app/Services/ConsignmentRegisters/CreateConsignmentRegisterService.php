<?php


namespace App\Services\ConsignmentRegisters;


use App\Models\ConsignmentRegisters\ConsignmentRegister;
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

        switch ($data['action']) {
            case ConsignmentRegister::ACTION_DRAFT:
                $customer_status = ConsignmentRegister::CUSTOMER_STATUS_DRAFT;
                $provider_status = ConsignmentRegister::PROVIDER_STATUS_DRAFT;
                break;
            case ConsignmentRegister::ACTION_APPROVE:
                $customer_status = ConsignmentRegister::CUSTOMER_STATUS_UNDER_CONSIDERATION;
                $provider_status = ConsignmentRegister::PROVIDER_STATUS_UNDER_CONSIDERATION;
                break;
            default:
                throw new BadRequestException('Action is required', 400);
        }

        return DB::transaction(function () use ($data, $customer_status, $provider_status) {
            $consignment_register = ConsignmentRegister::query()->create([
                'uuid' => Str::uuid(),
                'date' => Carbon::today()->format('Y-m-d'),
                'customer_status' => $customer_status,
                'contr_agent_status' => $provider_status,
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
                    'count' => $position['count'],
                    'vat_rate' => $position['vat_rate'],
                ]);
            }

            return $consignment_register;
        });
    }
}
