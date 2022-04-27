<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\PaymentRegisters\PaymentRegister;
use League\Fractal\TransformerAbstract;

class PaymentRegisterTransformer extends TransformerAbstract
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
    public function transform(PaymentRegister $payment_register)
    {
        $full_amount_payment = round(optional($payment_register)->positions->sum('amount_payment'), 2);
        return [
            'id' => $payment_register?->uuid,
            'number' => $payment_register?->number,
            'customer_status' => $payment_register?->customer_status,
            'provider_status' => $payment_register?->provider_status,
            'provider_contr_agent_id' => optional($payment_register->provider)->uuid,
            'contractor_contr_agent_id' => optional($payment_register->contractor)->uuid,
            'provider_contract_id' => optional($payment_register->provider_contract)->uuid,
            'responsible_full_name' => $payment_register?->responsible_full_name,
            'responsible_phone' => $payment_register?->responsible_phone,
            'comment' => $payment_register?->comment,
            'date' => $payment_register?->date,
            'full_amount_payment' => $full_amount_payment,
        ];
    }

    public function includePositions(PaymentRegister $payment_register)
    {
        if (optional($payment_register)->positions)
            return $this->collection($payment_register->positions, new PaymentRegisterPositionTransformer());
        return $this->null();
    }
}
