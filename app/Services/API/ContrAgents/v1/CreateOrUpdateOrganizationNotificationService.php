<?php


namespace App\Services\API\ContrAgents\v1;


use App\Events\NewStack;
use App\Models\IntegrationUser;
use App\Models\Notifications\OrganizationNotification;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\ContrAgent;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\SyncStacks\MTOSyncStack;
use App\Services\IService;

class CreateOrUpdateOrganizationNotificationService implements IService
{
    public function __construct(private $data, private IntegrationUser $user)
    {
    }

    public function run()
    {
        foreach ($this->data as $item) {
            $position_data = $item['positions'] ?? [];

            $provider_contr_agent = ContrAgent::query()->where(['name' => $item['provider_contr_agent']['name']])->first();
            $organization = Organization::query()->where(['name' => $item['organization']['name']])->first();


            $org_notification = collect([
                'uuid' => $item['id'],
                'date' => $item['date'],
                'organization_id' => $organization?->id,
                'provider_contr_agent_id' => $provider_contr_agent->id,
                'contract_number' => $item['contract_number'] ?? null,
                'contract_date' => $item['contract_date'] ?? null,
                'date_fact_delivery' => $item['date_fact_delivery'] ?? null,
                'delivery_address' => $item['delivery_address'] ?? null,
                'car_info' => $item['car_info'] ?? null,
                'driver_phone' => $item['driver_phone'] ?? null,
                'responsible_full_name' => $item['responsible_full_name'] ?? null,
                'responsible_phone' => $item['responsible_phone'] ?? null,
            ]);

            /** @var OrganizationNotification $org_notification */
            $org_notification = OrganizationNotification::query()->updateOrCreate([
                'uuid' => $org_notification['uuid']
            ], $org_notification->toArray());

            $position_ids = [];
            foreach ($position_data as $position) {
                $nomenclature = Nomenclature::query()
                    ->where([
                        'mnemocode' => $position['nomenclature']['mnemocode'],
                    ])
                    ->orWhere('name', $position['nomenclature']['name'])
                    ->first();
                $provider_order = ProviderOrder::query()->firstOrFail([
                    'uuid' => $position['order_id'],
                ]);

                $position = collect([
                    'position_id' => $position['position_id'],
                    'order_id' => $provider_order->id,
                    'price_without_vat' => $position['price_without_vat'] ?? null,
                    'nomenclature_id' => $nomenclature?->id,
                    'count' => $position['count'] ?? null,
                    'vat_rate' => $position['vat_rate'],
                ]);
                $org_notification->positions()->updateOrCreate([
                    'position_id' => $position['position_id'],
                ], $position->toArray());
                $position_ids[] = $position->id;
            }
            $org_notification->positions()->whereNotIn('id', $position_ids)->delete();

            if ($this->user->isProvider())
                event(new NewStack($org_notification,
                        new MTOSyncStack())
                );
        }
    }
}
