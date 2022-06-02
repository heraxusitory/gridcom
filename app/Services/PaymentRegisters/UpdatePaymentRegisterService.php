<?php


namespace App\Services\PaymentRegisters;


use App\Events\NewStack;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdatePaymentRegisterService implements IService
{
    public function __construct(private $payload, private PaymentRegister $payment_register)
    {
    }

    public function run()
    {
        $data = $this->payload;

        switch ($data['action']) {
            case PaymentRegister::ACTION_DRAFT:
//                $customer_status = PaymentRegister::CUSTOMER_STATUS_DRAFT;
                $provider_status = PaymentRegister::PROVIDER_STATUS_DRAFT;
                break;
            case PaymentRegister::ACTION_APPROVE:
//                $customer_status = PaymentRegister::CUSTOMER_STATUS_UNDER_CONSIDERATION;
                $provider_status = PaymentRegister::PROVIDER_STATUS_UNDER_CONSIDERATION;
                break;
            default:
                throw new BadRequestException('Action is required', 400);
        }

        return DB::transaction(function () use ($data,/* $customer_status,*/ $provider_status) {
            $this->payment_register->update([
//                'customer_status' => $customer_status,
                'provider_status' => $provider_status,
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
                'contractor_contr_agent_id' => $data['contractor_contr_agent_id'],
                'provider_contract_id' => $data['provider_contract_id'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
                'date' => Carbon::today()->format('Y-m-d'),
            ]);

            $position_ids = [];
            foreach ($data['positions'] as $position) {
                $position = $this->payment_register->positions()->updateOrCreate([
                    'position_id' => $position['position_id'] ?? null,
                ], [
                    'position_id' => $position['position_id'] ?? Str::uuid(),
                    'order_id' => $position['order_id'],
                    'payment_order_number' => $position['payment_order_number'],
                    'payment_order_date' => $position['payment_order_date'],
                    'amount_payment' => $position['amount_payment'],
                    'payment_type' => $position['payment_type'],
                ]);
                $position_ids[] = $position->id;
            }
            $this->payment_register->positions()->whereNotIn('id', $position_ids)->delete();

            event(new NewStack($this->payment_register,
                    (new ContractorSyncStack())->setContractor($this->payment_register->contractor),
                    (new ProviderSyncStack())->setProvider($this->payment_register->provider),
                /*new MTOSyncStack()*/)
            );

            if ($this->payment_register->provider_status === PaymentRegister::PROVIDER_STATUS_AGREED) {
                event(new NewStack($this->payment_register, new MTOSyncStack()));
            }

            return $this->payment_register;
        });
    }
}
