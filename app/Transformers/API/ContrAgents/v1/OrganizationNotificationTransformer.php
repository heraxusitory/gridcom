<?php

namespace App\Transformers\API\ContrAgents\v1;

use App\Models\Notifications\OrganizationNotification;
use League\Fractal\TransformerAbstract;

class OrganizationNotificationTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'positions'
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(OrganizationNotification $notification)
    {
        return [
            'id' => $notification?->uuid,
            'date' => $notification?->date,
            'status' => $notification?->status,
            'organization_id' => optional($notification->organization)->uuid,
            'provider_contr_agent_id' => optional($notification->provider)->uuid,
            'contract_stage' => $notification?->contract_stage,
            'contract_number' => $notification?->contract_number,
            'contract_date' => $notification?->contract_date,
            'date_fact_delivery' => $notification?->date_fact_delivery,
            'delivery_address' => $notification?->delivery_address,
            'car_info' => $notification?->car_info,
            'driver_phone' => $notification?->driver_phone,
            'responsible_full_name' => $notification?->responsible_full_name,
            'responsible_phone' => $notification?->responsible_phone,
            'organization_comment' => $notification?->organization_comment,
        ];
    }

    public function includePositions(OrganizationNotification $notification)
    {
        if (optional($notification)->positions)
            return $this->collection($notification->positions, new OrganizationNotificationPositionTransformer());
        return $this->null();
    }
}
