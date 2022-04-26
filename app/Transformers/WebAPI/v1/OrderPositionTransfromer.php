<?php

namespace App\Transformers\WebAPI\v1;

use App\Models\Orders\OrderPositions\OrderPosition;
use League\Fractal\TransformerAbstract;

class OrderPositionTransfromer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'nomenclature'
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
            'id' => $order_position?->id,
            'position_id' => $order_position?->position_id,
            'order_id' => $order_position?->order_id,
            'status' => $order_position?->status,
            'nomenclature_id' => $order_position?->nomenclature_id,
            'count' => $order_position?->count,
            'price_without_vat' => $order_position?->price_without_vat,
            'amount_without_vat' => $order_position?->amount_without_vat,
            'delivery_time' => $order_position?->delivery_time,
            'delivery_plan_time' => $order_position?->delivery_plan_time,
            'delivery_address' => $order_position?->delivery_address,
            'customer_comment' => $order_position?->customer_comment,
            'provider_comment' => $order_position?->provider_comment,
        ];
    }

    public function includeNomenclature(OrderPosition $order_position)
    {
        return $this->item($order_position?->nomenclature, new NomenclatureTransfromer());

    }
}
