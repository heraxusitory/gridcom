<?php

namespace App\Transformers\API\ContrAgents\v1;

use App\Models\ProviderOrders\Corrections\RequirementCorrectionPosition;
use League\Fractal\TransformerAbstract;

class RequirementCorrectionPositionTransformer extends TransformerAbstract
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
    public function transform(RequirementCorrectionPosition $position)
    {
        return [
            'position_id' => $position?->position_id,
            'status' => $position?->status,
        ];
    }
}
