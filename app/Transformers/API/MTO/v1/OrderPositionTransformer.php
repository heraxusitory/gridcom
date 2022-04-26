<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\Orders\OrderPositions\OrderPosition;
use League\Fractal\TransformerAbstract;

class OrderPositionTransformer extends TransformerAbstract
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
    public function transform(OrderPosition $order_position)
    {
        return [
            'position_id' => $order_position?->position_id,
            'status' => $order_position?->status,
            'nomenclature_id' => $order_position?->nomenclature_id,
            'count' => $order_position?->count,
            'price_without_vat' => $order_position?->price_without_vat,
            'amount_without_vat' => $order_position?->amount_without_vat,
            'delivery_time' => $order_position?->delivery_time,
            'delivery_address' => $order_position?->delivery_address,
        ];
    }
}
