<?php


namespace App\Services\OrganizationNotifications;


use App\Events\NewStack;
use App\Models\Notifications\OrganizationNotification;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateOrganizationNotificationService implements IService
{
    public function __construct(private $payload, private OrganizationNotification $organization_notification)
    {
    }

    public function run()
    {       //TODO: доделать сегодня
        $data = $this->payload;

        switch ($data['action']) {
            case OrganizationNotification::ACTION_DRAFT():
                $organization_status = OrganizationNotification::ORGANIZATION_STATUS_DRAFT;
                break;
            case OrganizationNotification::ACTION_APPROVE():
                $organization_status = OrganizationNotification::ORGANIZATION_STATUS_UNDER_CONSIDERATION;
                break;
            default:
                throw new BadRequestException('Action is required', 400);
        }

        return DB::transaction(function () use ($data, $organization_status) {
            $this->organization_notification->update([
                'date' => Carbon::today()->format('Y-m-d'),
                'status' => $organization_status,
                'organization_id' => $data['organization_id'],
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
//                'contract_stage' => $data['contract_stage'],
                'contract_number' => $data['contract_number'],
                'contract_date' => $data['contract_date'],
                'date_fact_delivery' => $data['date_fact_delivery'],
                'delivery_address' => $data['delivery_address'],
                'car_info' => $data['car_info'],
                'driver_phone' => $data['driver_phone'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['organization_comment'] ?? null,
            ]);

            $position_ids = [];
            foreach ($data['positions'] as $position) {
                $position = $this->organization_notification->positions()
                    ->updateOrCreate([
                        'position_id' => $position['position_id'] ?? null
                    ], [
                        'position_id' => $position['position_id'] ?? Str::uuid(),
                        'order_id' => $position['order_id'],
                        'nomenclature_id' => $position['nomenclature_id'],
                        'price_without_vat' => $position['price_without_vat'],
                        'count' => $position['count'],
                        'vat_rate' => $position['vat_rate'],
                    ]);
                $position_ids[] = $position->id;
            }

            $this->organization_notification->positions()
                ->whereNotIn('id', $position_ids)
                ->delete();

            event(new NewStack($this->organization_notification,
                    (new ProviderSyncStack())->setProvider($this->organization_notification->provider),
                    (new MTOSyncStack()))
            );

            return $this->organization_notification;
        });
    }
}
