<?php


namespace App\Services\PaymentRegisters;


use App\Models\PaymentRegisters\PaymentRegister;
use App\Services\IService;

class GetPaymentRegistersService implements IService
{
    public function __construct()
    {
    }

    public function run()
    {
        $payment_registers = PaymentRegister::query()->with([
            'positions'
        ])->paginate();
        return $payment_registers;
    }
}
