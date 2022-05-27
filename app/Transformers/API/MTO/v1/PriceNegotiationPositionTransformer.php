<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\PriceNegotiations\PriceNegotiationPosition;
use League\Fractal\TransformerAbstract;

class PriceNegotiationPositionTransformer extends TransformerAbstract
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
    public function transform(PriceNegotiationPosition $position)
    {
        return [
            'position_id' => $position?->position_id,
            'nomenclature_id' => optional($position->nomenclature)->uuid,
            'current_price_without_vat' => $position?->current_price_without_vat,
            'new_price_without_vat' => $position?->new_price_without_vat,
            'agreed_price' => $position?->agreed_price,
        ];
    }
}
