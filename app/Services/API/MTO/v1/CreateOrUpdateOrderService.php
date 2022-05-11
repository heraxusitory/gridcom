<?php


namespace App\Services\API\MTO\v1;


use App\Events\NewStack;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Provider;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrUpdateOrderService implements IService
{

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function run()
    {
        foreach ($this->data as $item) {
            DB::transaction(function () use ($item) {
                $customer_data = $item['order_customer'] ?? [];
                $provider_data = $item['order_provider'] ?? [];
                $contractor_data = $item['order_contractor'] ?? [];
                $position_data = $item['order_positions'] ?? [];

                $organization = Organization::query()->firstOrCreate([
                    'uuid' => $customer_data['organization_id'],
                ]);
                $work_agreement = WorkAgreementDocument::query()->firstOrCreate([
                    'uuid' => $customer_data['work_agreement_id'],
                ]);
                $customer_object = CustomerObject::query()->firstOrCreate([
                    'uuid' => $customer_data['object_id'],
                ]);
                if (isset($customer_data['sub_object_id'])) {
                    $customer_sub_object = CustomerSubObject::query()->firstOrCreate([
                        'uuid' => $customer_data['sub_object_id'],
                    ], [
                        'customer_object_id' => $customer_object->id
                    ]);
                }
//                    $customer_sub_object = $customer_object->subObjects()->firstOrCreate([
//                        'uuid' => $customer_data['sub_object_id'],
//                    ]);
                $customer['organization_id'] = $organization->id;
                $customer['work_agreement_id'] = $work_agreement->id;
                $customer['object_id'] = $customer_object->id;
                $customer['sub_object_id'] = isset($customer_sub_object) ? $customer_sub_object->id : null;
                $customer['work_type'] = $customer_data['work_type'];

                if (isset($provider_data['contr_agent_id'])) {
                    /** @var ContrAgent $provider_contr_agent */
                    $provider_contr_agent = ContrAgent::query()->firstOrCreate([
                        'uuid' => $provider_data['contr_agent_id'],
                    ]);
                } elseif (isset($provider_data['contr_agent_name'])) {
                    /** @var ContrAgent $provider_contr_agent */
                    $provider_contr_agent = ContrAgent::query()->firstOrCreate([
                        'name' => $provider_data['contr_agent_name'],
                    ], [
                        'uuid' => Str::uuid(),
                    ]);
                }

                if (isset($provider_data['provider_contract_id'])) {
                    $provider_contract = ProviderContractDocument::query()->firstOrCreate([
                        'uuid' => $provider_data['provider_contract_id'],
                    ]);
                } elseif (isset($provider_data['provider_contract_name'])) {
                    $provider_contract = ProviderContractDocument::query()->firstOrCreate([
                        'number' => $provider_data['provider_contract_name'],
                    ], [
                        'uuid' => Str::uuid(),
                    ]);
                }

                $provider['contr_agent_id'] = isset($provider_contr_agent) ? $provider_contr_agent->id : null;
                $provider['provider_contract_id'] = isset($provider_contract) ? $provider_contract->id : null;

                /** @var ContrAgent $contractor_contr_agent */
                $contractor_contr_agent = ContrAgent::query()->firstOrCreate([
                    'uuid' => $contractor_data['contr_agent_id'],
                ]);
                $contractor['contr_agent_id'] = $contractor_contr_agent->id;

                $order_data = collect([
                    'uuid' => $item['id'],
                    'number' => $item['number'] ?? null,
                    'order_date' => $item['order_date'] ?? null,
                    'deadline_date' => $item['deadline_date'] ?? null,
                    'customer_status' => $item['customer_status'],
                    'provider_status' => $item['provider_status'],
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
                        ]);

                        return Order::withoutEvents(function () use ($item, $customer, $provider, $contractor, $order_data) {
                            return Order::query()->create(
                                $order_data->toArray()
                            /*[
                                'uuid' => $item['id'],
                                'number' => $item['number'] ?? null,
                                'order_date' => isset($item['order_date']) ? (new Carbon($item['order_date']))->format('d.m.Y') : null,
                                'deadline_date' => $item['deadline_date'] ?? null,
                                'customer_status' => $item['customer_status'],
                                'provider_status' => $item['provider_status'],
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
                    $order->update($order_data->toArray()/*[
                        'number' => $item['number'] ?? null,
                        'order_date' => (new Carbon($item['order_date']))->format('d.m.Y'),
                        'deadline_date' => $item['deadline_date'] ?? null,
                        'customer_status' => $item['customer_status'],
                        'provider_status' => $item['provider_status'],
                    ]*/);
                }

                $position_ids = [];
                foreach ($position_data as $position) {
                    $nomenclature = Nomenclature::query()->firstOrCreate([
                        'uuid' => $position['nomenclature_id'],
                    ]);
                    $position = collect([
                        'position_id' => $position['position_id'],
                        'status' => $position['status'] ?? null,
                        'nomenclature_id' => $nomenclature->id,
                        'count' => $position['count'] ?? null,
                        'price_without_vat' => $position['price_without_vat'] ?? null,
                        'amount_without_vat' => $position['amount_without_vat'] ?? null,
                        'delivery_time' => $position['delivery_time'] ?? null,
                        'delivery_address' => $position['delivery_address'] ?? null,
                        'customer_comment' => $position['customer_comment'] ?? null,
                    ]);
                    $position = $order->positions()->updateOrCreate(['position_id' => $position['position_id']], $position->toArray());
                    $position_ids[] = $position->id;
                }
                $order->positions()->whereNotIn('id', $position_ids)->delete();

                event(new NewStack($order, new ContractorSyncStack($contractor_contr_agent), new ProviderSyncStack($provider_contr_agent)));
            });
        }
    }
}
