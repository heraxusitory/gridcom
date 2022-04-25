<?php

namespace App\Transformers;

use App\Models\Orders\Order;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract
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
    public function transform(Order $order)
    {
        return [
            'number' => optional($order)->number,
            'order_date' => optional($order)->order_date,
            'customer_status' => optional($order)->customer_status,
            'provider_status' => optional($order)->provider_status,
            'deadline_date' => optional($order)->deadline_date,
            'customer' => [
                'organization_id' => optional($order->customer)->organization_id,
                'work_agreement_id' => optional($order->customer)->work_agreement_id,
                'work_type' => optional($order->customer)->work_type,
                'object_id' => optional($order->customer)->object_id,
                'sub_object_id' => optional($order->customer)->sub_object_id,
                'work_start_date' => optional($order->customer)->work_start_date,
                'work_end_date' => optional($order->customer)->work_end_date,
            ],
            'provider' => [
                'contr_agent_id' => optional($order->provider)->contr_agent_id,
                'contract_id' => optional($order->provider->contract)->id,
                'contact' => [
                    'full_name' => optional($order->provider)->full_name,
                    'email' => optional($order->provider)->email,
                    'phone' => optional($order->provider)->phone,
                ]
            ],
            'contractor' => [
                'contact' => ['contr_agent_id' => optional($order->contractor)->contr_agent_id,
                    'full_name' => optional($order->contractor)->full_name,
                    'email' => optional($order->contractor)->email,
                    'phone' => optional($order->contractor)->phone,
                ],
                'responsible_full_name' => optional($order->contractor)->responsible_full_name,
                'responsible_phone' => optional($order->contractor)->responsible_phone,
            ],

            'positions' => optional($order)->positions,
        ];
    }
}
