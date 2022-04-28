<?php


namespace App\Services\PaymentRegisters;


use App\Models\PaymentRegisters\PaymentRegister;
use App\Services\IService;
use Illuminate\Support\Facades\Auth;

class GetPaymentRegistersService implements IService
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function run()
    {
        $payment_registers = PaymentRegister::query()->with([
            'positions'
        ]);

        if ($this->user->isProvider()) {
            $payment_registers->where('provider_contr_agent_id', $this->user->contr_agent_id());
        } elseif ($this->user->isContractor()) {
            $payment_registers->where('contractor_contr_agent_id', $this->user->contr_agent_id());
        }
        return $payment_registers->get();
    }
}
