<?php


namespace App\Services\PaymentRegisters;


use App\Models\PaymentRegisters\PaymentRegister;
use App\Services\IService;
use Illuminate\Support\Facades\Auth;

class GetPaymentRegisterService implements IService
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct(private $payment_register_id)
    {
        $this->user = auth('webapi')->user();
    }

    public function run()
    {
        $payment_register = PaymentRegister::query()->with([
            'positions.order'
        ]);
        if ($this->user->isProvider()) {
            $payment_register->where('provider_contr_agent_id', $this->user->contr_agent_id());
        } elseif ($this->user->isContractor()) {
            $payment_register->where('contractor_contr_agent_id', $this->user->contr_agent_id());
        }
        return $payment_register->findOrFail($this->payment_register_id);
    }
}
