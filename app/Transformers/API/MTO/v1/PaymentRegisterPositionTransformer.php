<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\PaymentRegisters\PaymentRegisterPosition;
use League\Fractal\TransformerAbstract;

class PaymentRegisterPositionTransformer extends TransformerAbstract
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
    public function transform(PaymentRegisterPosition $payment_register_position)
    {
        return [
            'position_id' => $payment_register_position?->position_id,
            'order_id' => optional($payment_register_position->order)->uuid,
            'payment_order_date' => $payment_register_position?->payment_order_date,
            'payment_order_number' => $payment_register_position?->payment_order_number,
            'amount_payment' => $payment_register_position?->amount_payment,
            'payment_type' => $payment_register_position?->payment_type,
            'object_id' => optional($payment_register_position->order->customer->object)->uuid,
            'organization_id' => optional($payment_register_position->order->customer->organization)->uuid,
            'work_agreement_id' => optional($payment_register_position->order->customer->contract)->uuid,
        ];
    }
}
