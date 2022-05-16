<?php


namespace App\Services\API\ContrAgents\v1;


use App\Events\NewStack;
use App\Models\IntegrationUser;
use App\Models\Orders\Order;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\Provider;
use App\Models\References\ContrAgent;
use App\Models\References\ProviderContractDocument;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateOrUpdatePaymentRegisterService implements IService
{
    public function __construct(private $data, private IntegrationUser $user)
    {
    }

    public function run()
    {
        foreach ($this->data as $item) {
            DB::transaction(function () use ($item) {
                $position_data = $item['positions'] ?? [];

                /** @var ContrAgent $provider_contr_agent */
                $provider_contr_agent = $this->user->isProvider() ? $this->user->contr_agent()->firstOrFail() : ContrAgent::query()->where([
                    'name' => $item['provider_contr_agent']['name'],
                ])->first();
                /** @var ContrAgent $contractor_contr_agent */
                $contractor_contr_agent = $this->user->isContractor() ? $this->user->contr_agent()->firstOrFail() : ContrAgent::query()->where([
                    'name' => $item['contractor_contr_agent']['name']
                ])->first();

                if ($this->user->isContractor()) {
                    /** @var PaymentRegister $payment_register */
                    $payment_register = PaymentRegister::withoutEvents(function () use ($contractor_contr_agent, $provider_contr_agent, $item) {
                        $provider_contract = ProviderContractDocument::query()->where([
                            'number' => $item['provider_contract']['number'],
                        ])->first();

                        $pr_data = collect([
                            'uuid' => $item['id'],
                            'number' => $item['number'],
                            'provider_status' => $item['provider_status'],
                            'provider_contr_agent_id' => $provider_contr_agent->id,
                            'provider_contract_id' => $provider_contract->id,
                            'contractor_contr_agent_id' => $contractor_contr_agent->id,
                            'responsible_full_name' => $item['responsible_full_name'],
                            'responsible_phone' => $item['responsible_phone'],
                            'comment' => $item['comment'],
                            'date' => (new Carbon($item['date']))->format('d.m.Y'),
                        ]);
                        return PaymentRegister::query()->updateOrCreate([
                            'uuid' => $pr_data['uuid']
                        ], $pr_data->toArray());
                    });

                    $position_ids = [];
                    foreach ($position_data as $position) {
                        $order = Order::query()->firstOrCreate(['uuid' => $position['order_id']]);

                        $pr_position_data = collect([
                            'position_id' => $position['position_id'],
                            'payment_order_number' => $position['payment_order_number'] ?? null,
                            'order_id' => $order->id,
                            'payment_order_date' => $position['payment_order_date'] ?? null,
                            'amount_payment' => $position['amount_payment'] ?? null,
                            'payment_type' => $position['payment_type'],
                        ]);
                        $position = $payment_register->positions()->updateOrCreate([
                            'position_id' => $position['position_id'],
                        ], $pr_position_data->toArray());
                        $position_ids[] = $position->id;
                    }
                    $payment_register->positions()->whereNotIn('id', $position_ids)->delete();

                    event(new NewStack($payment_register,
                            (new ProviderSyncStack())->setProvider($provider_contr_agent))
                    );
                }

                if ($this->user->isProvider()) {
                    $payment_register = PaymentRegister::query()->where('uuid', $item['id'])->first();

                    if ($payment_register->provider_status === PaymentRegister::PROVIDER_STATUS_UNDER_CONSIDERATION &&
                        in_array($item['provider_status'], [PaymentRegister::PROVIDER_STATUS_AGREED, PaymentRegister::PROVIDER_STATUS_NOT_AGREED])) {
                        $payment_register->provider_status = $item['provider_status'];
                        $payment_register->push();

                        event(new NewStack($payment_register,
                                (new ContractorSyncStack())->setContractor($contractor_contr_agent))
                        );
                    }
                }
            });
        }
    }
}
