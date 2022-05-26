<?php


namespace App\Transformers\API\ContrAgents\v1;


use App\Models\ProviderOrders\ProviderOrder;
use League\Fractal\TransformerAbstract;

class ProviderOrderTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'base_positions',
        'actual_positions',
    ];

    public function transform(ProviderOrder $order)
    {
        return [
            'id' => $order->uuid,
            'number' => $order->number,
            'order_date' => $order->order_date,
            'contract_number' => $order->contract_number,
            'contract_date' => $order->contract_date,
            'contract_stage' => $order->contract_stage,
            'provider_contr_agent' => [
                'name' => $order->provider?->name,
            ],
            'organization' => [
                'name' => $order->organization?->name,
            ],
            'responsible_full_name' => $order->responsible_full_name,
            'responsible_phone' => $order->responsible_phone,
            'organization_comment' => $order->organization_comment,
        ];
    }

    public function includeBasePositions(ProviderOrder $order)
    {
        if (optional($order)->base_positions)
            return $this->collection($order->base_positions, new BaseProviderOrderPositionTransformer());
        return $this->null();
    }

    public function includeActualPositions(ProviderOrder $order)
    {
        if (optional($order)->actual_positions)
            return $this->collection($order->actual_positions, new ActualProviderOrderPositionTransformer());
        return $this->null();
    }
}
