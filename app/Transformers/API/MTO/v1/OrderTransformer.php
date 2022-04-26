<?php

namespace App\Transformers\API\MTO\v1;

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
        'positions',
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Order $order)
    {
        $data = [
            'id' => $order->uuid,
            'number' => optional($order)->number,
            'order_date' => optional($order)->order_date,
            'deadline_date' => optional($order)->deadline_date,
            'customer_status' => optional($order)->customer_status,
            'provider_status' => optional($order)->provider_status,
        ];

        $customer = [
            'organization_id' => optional($order->customer->organization)->uuid,
            'work_agreement_id' => optional($order->customer->contract)->uuid,
            'work_type' => optional($order->customer)->work_type,
            'object_id' => optional($order->customer->object)->uuid,
            'sub_object_id' => optional($order->customer->subObject)->uuid,
            'work_start_date' => optional($order->customer)->work_start_date,
            'work_end_date' => optional($order->customer)->work_end_date,
        ];

        $provider = [
            'contr_agent_id' => optional($order->provider->contr_agent)->uuid,
            'provider_contract_id' => optional($order->provider->contract)->uuid,
            'full_name' => optional($order->provider)->full_name,
            'email' => optional($order->provider)->email,
            'phone' => optional($order->provider)->phone,
        ];

        $contractor = [
            'contr_agent_id' => optional($order->contractor->contr_agent)->uuid,
            'full_name' => optional($order->contractor)->full_name,
            'email' => optional($order->contractor)->email,
            'phone' => optional($order->contractor)->phone,
            'contractor_responsible_full_name' => optional($order->contractor)->contractor_responsible_full_name,
            'contractor_responsible_phone' => optional($order->contractor)->contractor_responsible_phone,
        ];

        $data['order_customer'] = $customer;
        $data['order_contractor'] = $contractor;
        $data['order_provider'] = $provider;
        return $data;
    }

    public function includePositions(Order $order)
    {
        return $this->collection(optional($order)->positions, new OrderPositionTransformer());
    }
}
