<?php


namespace App\Services\ContractorNotifications;


use App\Models\Notifications\ContractorNotification;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateContractorNotificationService implements IService
{
    public function __construct(private $payload, private ContractorNotification $contractor_notification)
    {
    }

    public function run()
    {
        $data = $this->payload;

        switch ($data['action']) {
            case ContractorNotification::ACTION_DRAFT():
                $contractor_status = ContractorNotification::CONTRACTOR_STATUS_DRAFT;
                break;
            case ContractorNotification::ACTION_APPROVE():
                $contractor_status = ContractorNotification::CONTRACTOR_STATUS_UNDER_CONSIDERATION;
                break;
            default:
                throw new BadRequestException('Action is required', 400);
        }

        return DB::transaction(function () use ($data, $contractor_status) {
            $this->contractor_notification->update([
                'date' => Carbon::today()->format('d.m.Y'),
                'status' => $contractor_status,
                'contractor_contr_agent_id' => $data['contractor_contr_agent_id'],
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
                'provider_contract_id' => $data['provider_contract_id'],
                'date_fact_delivery' => $data['date_fact_delivery'],
                'delivery_address' => $data['delivery_address'],
                'car_info' => $data['car_info'],
                'driver_phone' => $data['driver_phone'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
            ]);

            $position_ids = [];
            foreach ($data['positions'] as $position) {
                $position = $this->contractor_notification->positions()
                    ->updateOrCreate([
                        'position_id' => $position['position_id'] ?? null
                    ], [
                        'position_id' => $position['position_id'] ?? Str::uuid(),
                        'order_id' => $position['order_id'],
                        'nomenclature_id' => $position['nomenclature_id'],
                        'count' => $position['count'],
                        'vat_rate' => $position['vat_rate'],
                    ]);
                $position_ids[] = $position->id;
            }

            $this->contractor_notification->positions()
                ->whereNotIn('id', $position_ids)
                ->delete();

            return $this->contractor_notification;
        });
    }
}
