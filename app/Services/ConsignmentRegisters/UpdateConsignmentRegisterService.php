<?php


namespace App\Services\ConsignmentRegisters;


use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateConsignmentRegisterService implements IService
{
    public function __construct(private $payload, private ConsignmentRegister $consignment_register)
    {
    }

    public function run()
    {
        $data = $this->payload;

        $this->consignment_register->is_approved = match ($data['action']) {
            ConsignmentRegister::ACTION_DRAFT => false,
            ConsignmentRegister::ACTION_APPROVE => true,
            default => throw new BadRequestException('Action is required', 400),
        };

        switch ($data['action']) {
            case ConsignmentRegister::ACTION_DRAFT:
                $this->consignment_register->is_approved = false;
                $customer_status = ConsignmentRegister::CUSTOMER_STATUS_DRAFT;
                $provider_status = ConsignmentRegister::PROVIDER_STATUS_DRAFT;
                break;
            case ConsignmentRegister::ACTION_APPROVE:
                $this->consignment_register->is_approved = true;
                $customer_status = ConsignmentRegister::CUSTOMER_STATUS_UNDER_CONSIDERATION;
                $provider_status = ConsignmentRegister::CUSTOMER_STATUS_UNDER_CONSIDERATION;
                break;
            default:
                throw new BadRequestException('Action is required', 400);

        }

        return DB::transaction(function () use ($data, $customer_status, $provider_status) {
            $this->consignment_register->update([
                'date' => Carbon::today()->format('d.m.Y'),
                'customer_status' => $customer_status,
                'provider_status' => $provider_status,
                'organization_id' => $data['organization_id'],
                'contractor_contr_agent_id' => $data['contractor_contr_agent_id'],
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
                'customer_object_id' => $data['customer_object_id'],
                'customer_sub_object_id' => $data['customer_sub_object_id'],
                'work_agreement_id' => $data['work_agreement_id'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
            ]);

            $position_ids = [];
            foreach ($data['positions'] as $position) {
                $position = $this->consignment_register->positions()->updateOrCreate([
                    'position_id' => $position['position_id'] ?? null,
                ], [
                    'position_id' => $position['position_id'] ?? Str::uuid(),
                    'consignment_id' => $position['consignment_id'],
                    'nomenclature_id' => $position['nomenclature_id'],
                    'count' => $position['count'],
                    'vat_rate' => $position['vat_rate'],
                ]);
                $position_ids[] = $position->id;
            }

            $this->consignment_register->positions()
                ->whereNotIn('id', $position_ids)
                ->delete();

            return $this->consignment_register;
        });
    }
}