<?php


namespace App\Services\PaymentRegisters;


use App\Models\PaymentRegisters\PaymentRegister;
use App\Services\IService;

class GetPaymentRegisterService implements IService
{
    public function __construct(private $payment_register_id)
    {
    }

    public function run()
    {
        $payment_register = PaymentRegister::query()->with([
            'positions'
        ])->findOrFail($this->payment_register_id);
        return $payment_register;
    }
}
