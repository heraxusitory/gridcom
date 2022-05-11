<?php


namespace App\Services\API\ContrAgents\v1;


use App\Events\NewStack;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Provider;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateOrderService implements IService
{

    public function __construct(private $data, private $user)
    {

    }

    public function run()
    {
        foreach ($this->data as $item) {
            DB::transaction(function () use ($item) {
                $customer_data = $item['order_customer'] ?? [];
                $provider_data = $item['order_provider'] ?? [];
                $contractor_data = $item['order_contractor'] ?? [];
                $position_data = $item['order_positions'] ?? [];

                $organization = Organization::query()
                    ->where(['name' => $customer_data['organization']['name']])
                    ->first();
                $work_agreement = WorkAgreementDocument::query()
                    ->where(['number' => $customer_data['work_agreement']['number']])
                    ->first();
                $customer_object = CustomerObject::query()
                    ->where(['name' => $customer_data['object']['name']])
                    ->first();
                $customer_sub_object = $customer_object?->subObjects()
                    ->where(['uuid' => $customer_data['sub_object_id']])
                    ->first();
                $customer['organization_id'] = $organization?->id;
                $customer['work_agreement_id'] = $work_agreement?->id;
                $customer['object_id'] = $customer_object?->id;
                $customer['sub_object_id'] = $customer_sub_object?->id;

                $provider_contr_agent = ContrAgent::query()
                    ->where(['name' => $provider_data['contr_agent']['name']])
                    ->first();

                $provider_contract = ProviderContractDocument::query()
                    ->where(['number' => $provider_data['provider_contract']['number']])
                    ->first();

                $provider['contr_agent_id'] = $provider_contr_agent?->id;
                $provider['provider_contract_id'] = $provider_contract?->id;

                $contractor_contr_agent = $this->user->contr_agent()->firstOrFail();
                $contractor['contr_agent_id'] = $contractor_contr_agent->id;

                $order_data = collect([
                    'uuid' => $item['id'],
                    'number' => $item['number'] ?? null,
                    'order_date' => $item['order_date'] ?? null,
                    'deadline_date' => $item['deadline_date'] ?? null,
//                    'provider_status' => Order::PROVIDER_STATUS_UNDER_CONSIDERATION,
                ]);

                $order = Order::query()->where('uuid', $order_data['uuid'])->firstOr(
                //Если обьект новый и его нужно создать
                    function () use ($item, $customer, $provider, $contractor, $order_data) {
                        $customer = Customer::query()->create($customer);
                        $provider = Provider::query()->create($provider);
                        $contractor = Contractor::query()->create($contractor);

                        $order_data->merge([
                            'customer_id' => $customer->id,
                            'provider_id' => $provider->id,
                            'contractor_id' => $contractor->id,
                            'customer_status' => Order::CUSTOMER_STATUS_UNDER_CONSIDERATION,
                            'provider_status' => Order::PROVIDER_STATUS_UNDER_CONSIDERATION,
                        ]);

                        return Order::withoutEvents(function () use ($item, $customer, $provider, $contractor, $order_data) {
                            return Order::query()->create(
                                $order_data->toArray()
                            /* [
                                 'uuid' => $item['id'],
                                 'number' => $item['number'] ?? null,
                                 'order_date' => isset($item['order_date']) ? (new Carbon($item['order_date']))->format('d.m.Y') : null,
                                 'deadline_date' => $item['deadline_date'] ?? null,
//                                    'customer_status' => $item['customer_status'],
//                                    'provider_status' => $item['provider_status'],
                                 'customer_id' => $customer->id,
                                 'provider_id' => $provider->id,
                                 'contractor_id' => $contractor->id,
                             ]*/);
                        });
                    });

                //Если обьект существует и его нужно обновить
                if (!$order->wasRecentlyCreated) {
                    $order->customer()->update($customer);
                    $order->provider()->update($provider);
                    $order->contractor()->update($contractor);
                    $order->update($order_data->toArray()
                    /* 'number' => $item['number'] ?? null,
                     'order_date' => isset($item['order_date']) ? (new Carbon($item['order_date']))->format('d.m.Y') : null,
                     'deadline_date' => $item['deadline_date'] ?? null,*/
//                        'customer_status' => $item['customer_status'],
//                        'provider_status' => $item['provider_status'],
                    );
                }

                $position_ids = [];
                foreach ($position_data as $position) {
                    $nomenclature = Nomenclature::query()
                        ->where(['uuid' => $position['nomenclature']['id']])
                        ->orWhere(['mnemocode' => $position['nomenclature']['mnemocode']])
                        ->first();

                    $position = collect([
                        'position_id' => $position['position_id'],
                        'status' => $position['status'] ?? null,
                        'nomenclature_id' => $nomenclature->id,
                        'count' => $position['count'] ?? null,
                        'price_without_vat' => $position['price_without_vat'] ?? null,
                        'amount_without_vat' => $position['amount_without_vat'] ?? null,
                        'delivery_time' => $position['delivery_time'] ?? null,
                        'delivery_address' => $position['delivery_address'] ?? null,
                    ]);

                    $position = $order->positions()->updateOrCreate(['position_id' => $position['position_id']], $position->toArray());
                    $position_ids[] = $position->id;
                }
                $order->positions()->whereNotIn('id', $position_ids)->delete();

                event(new NewStack($order, new ProviderSyncStack($order->provider->contr_agent), new MTOSyncStack()));

            });
        }
    }
}