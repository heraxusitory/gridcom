<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\Consignments\ConsignmentPosition;
use League\Fractal\TransformerAbstract;

class ConsignmentPositionTransformer extends TransformerAbstract
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
    public function transform(ConsignmentPosition $consignment_position)
    {
        return [
            'position_id' => $consignment_position?->position_id,
            'order_id' => optional($consignment_position->order)->uuid,
            'nomenclature_id' => optional($consignment_position->nomenclature)->uuid,
            'count' => $consignment_position?->count,
            'price_without_vat' => $consignment_position?->price_without_vat,
            'amount_without_vat' => $consignment_position?->amount_without_vat,
            'vat_rate' => $consignment_position?->vat_rate,
            'amount_with_vat' => $consignment_position?->amount_with_vat,
            'country' => $consignment_position?->country,
            'cargo_custom_declaration' => $consignment_position?->cargo_custom_declaration,
            'declaration' => $consignment_position?->declaration,
        ];
    }
}
