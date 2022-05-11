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
            'order' => optional($position->order)->uuid,
            'nomenclature_id' => optional($position->nomenclature)->uuid,
            'count' => $position?->count,
            'vat_rate' => $position?->vat_rate,
        ];
    }
}
