<?php

namespace App\Transformers\API\ContrAgents\v1;

use App\Models\ProviderOrders\Corrections\RequirementCorrection;
use League\Fractal\TransformerAbstract;

class RequirementCorrectionTransformer extends TransformerAbstract
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
    public function transform(RequirementCorrection $requirement_correction)
    {
        return [
            'id' => $requirement_correction?->correction_id
        ];
    }

    public function includePositions(RequirementCorrection $requirement_correction)
    {
        if (optional($requirement_correction)->positions)
            return $this->collection($requirement_correction->positions, new RequirementCorrectionPositionTransformer());
        return $this->null();
    }
}
