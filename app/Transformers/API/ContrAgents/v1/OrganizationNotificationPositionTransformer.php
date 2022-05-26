<?php

namespace App\Transformers\API\ContrAgents\v1;

use App\Models\Notifications\OrganizationNotificationPosition;
use League\Fractal\TransformerAbstract;

class OrganizationNotificationPositionTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
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
    public function transform(OrganizationNotificationPosition $position)
    {
        return [
            'position_id' => $position?->position_id,
            'order_id' => optional($position->order)->uuid,
            'nomenclature' => [
                'name' => optional($position->nomenclature)->name,
                'mnemocode' => optional($position->nomenclature)->mnemocode,
            ],
            'price_without_vat' => $position->price_without_vat,
            'count' => $position?->count,
            'vat_rate' => $position?->vat_rate,
        ];
    }
}
