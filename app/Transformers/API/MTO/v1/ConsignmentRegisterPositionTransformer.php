<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\ConsignmentRegisters\ConsignmentRegisterPosition;
use League\Fractal\TransformerAbstract;

class ConsignmentRegisterPositionTransformer extends TransformerAbstract
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
    public function transform(ConsignmentRegisterPosition $consignment_register_position)
    {
        return [
            'position_id' => $consignment_register_position?->position_id,
            'consignment_id' => optional($consignment_register_position->consignment)->uuid,
            'nomenclature_id' => optional($consignment_register_position->nomenclature)->uuid,
            'count' => $consignment_register_position?->count,
            'vat_rate' => $consignment_register_position?->vat_rate,
            'result_status' => $consignment_register_position?->result_status,
        ];
    }
}
