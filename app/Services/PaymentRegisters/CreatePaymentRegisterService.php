<?php


namespace App\Services\PaymentRegisters;


use App\Models\PaymentRegisters\PaymentRegister;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CreatePaymentRegisterService implements IService
{
    public function __construct(private $payload)
    {
    }

    public function run()
    {
        $data = $this->payload;

        switch ($data['action']) {
            case PaymentRegister::ACTION_DRAFT:
                $customer_status = PaymentRegister::CUSTOMER_STATUS_DRAFT;
                $provider_status = PaymentRegister::PROVIDER_STATUS_DRAFT;
                break;
            case PaymentRegister::ACTION_APPROVE:
                $customer_status = PaymentRegister::CUSTOMER_STATUS_UNDER_CONSIDERATION;
                $provider_status = PaymentRegister::PROVIDER_STATUS_UNDER_CONSIDERATION;
                break;
            default:
                throw new BadRequestException('Action is required', 400);
        }

        return DB::transaction(function () use ($data, $customer_status, $provider_status) {
            $payment_register = PaymentRegister::query()->create([
                'uuid' => Str::uuid(),
//                'customer_status' => $customer_status,
                'provider_status' => $provider_status,
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
                'contractor_contr_agent_id' => $data['contractor_contr_agent_id'],
                'provider_contract_id' => $data['provider_contract_id'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
                'date' => Carbon::today()->format('d.m.Y'),
            ]);

            foreach ($data['positions'] as $position) {
                $payment_register->positions()->create([
                    'position_id' => Str::uuid(),
                    'order_id' => $position['order_id'],
                    'payment_order_number' => $position['payment_order_number'],
                    'payment_order_date' => $position['payment_order_date'],
                    'amount_payment' => $position['amount_payment'],
                    'payment_type' => $position['payment_type'],
                ]);
            }

            return $payment_register;
        });
    }
}
