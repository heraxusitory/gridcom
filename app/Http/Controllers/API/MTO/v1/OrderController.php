<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\Provider;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\OrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required|array',
            'orders.*.id' => 'required|uuid',
            'orders.*.number' => 'nullable|string|max:255',
            'orders.*.deadline_date' => 'nullable|date_format:Y-m-d',
            'orders.*.customer_status' => [Rule::in(Order::getCustomerStatuses())],
            'orders.*.provider_status' => [Rule::in(Order::getProviderStatuses())],

            'orders.*.order_customer.organization_id' => 'required|uuid',
            'orders.*.order_customer.work_agreement_id' => 'required|uuid',
            'orders.*.order_customer.work_type' => 'required|string|max:255',
            'orders.*.order_customer.object_id' => 'required|uuid',
            'orders.*.order_customer.sub_object_id' => 'required|uuid',
            'orders.*.order_customer.work_start_date' => 'required|date_format:Y-m-d',
            'orders.*.order_customer.work_end_date' => 'required|date_format:Y-m-d',

            'orders.*.order_provider.provider_contract_id' => 'nullable|required_without:orders.*.order_provider.provider_contract_name|uuid',
            'orders.*.order_provider.provider_contract_name' => 'nullable|required_without:orders.*.order_provider.provider_contract_id|string|max:255',
//            'orders.order_provider.contact_id' => 'required|uuid|exists:contact_persons,uuid',
            'orders.*.order_provider.contr_agent_id' => 'nullable|required_without:orders.*.order_provider.contr_agent_name|uuid',
            'orders.*.order_provider.contr_agent_name' => 'nullable|required_without:orders.*.order_provider.contr_agent_id|string|max:255',
            'orders.*.order_provider.full_name' => 'nullable|string|max:255',
            'orders.*.order_provider.email' => 'nullable|string|max:255',
            'orders.*.order_provider.phone' => 'nullable|string|max:255',

//            'orders.order_contractor.contact_id' => 'required|uuid|exists:contact_persons,uuid',
            'orders.*.order_contractor.contr_agent_id' => 'required|uuid',
            'orders.*.order_contractor.full_name' => 'nullable|string|max:255',
            'orders.*.order_contractor.email' => 'nullable|string|max:255',
            'orders.*.order_contractor.phone' => 'nullable|string|max:255',
            'orders.*.order_contractor.contractor_responsible_full_name' => 'nullable|string|max:255',
            'orders.*.order_contractor.contractor_responsible_phone' => 'nullable|string|max:255',

            'orders.*.order_positions' => 'nullable|array',
            'orders.*.order_positions.*.position_id' => 'required|uuid',
            'orders.*.order_positions.*.status' => ['nullable', Rule::in(OrderPosition::getStatuses())],
            'orders.*.order_positions.*.nomenclature_id' => 'required|uuid',
//            'orders.*.order_positions.*.unit_id' => 'required|uuid|exists:nomenclature_units,uuid',
            'orders.*.order_positions.*.count' => 'nullable|numeric',
            'orders.*.order_positions.*.price_without_vat' => 'nullable|numeric',
            'orders.*.order_positions.*.amount_without_vat' => 'nullable|numeric',
            'orders.*.order_positions.*.delivery_time' => 'nullable|date_format:Y-m-d',
            'orders.*.order_positions.*.delivery_address' => 'nullable|string|max:255',
        ])->validate();

        $data = $request->all()['orders'];

        try {
            foreach ($data as $item) {
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
                    $customer_sub_object = $customer_object->subObjects()->firstOrCreate([
                        'uuid' => $customer_data['sub_object_id'],
                    ]);
                    $customer_data['organization_id'] = $organization->id;
                    $customer_data['work_agreement_id'] = $work_agreement->id;
                    $customer_data['object_id'] = $customer_object->id;
                    $customer_data['sub_object_id'] = $customer_sub_object->id;

                    if (isset($provider_data['contr_agent_id'])) {
                        $provider_contr_agent = ContrAgent::query()->firstOrCreate([
                            'uuid' => $provider_data['contr_agent_id'],
                        ]);
                    } elseif (isset($provider_data['contr_agent_name'])) {
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

                    $provider_data['contr_agent_id'] = $provider_contr_agent->id;
                    $provider_data['provider_contract_id'] = $provider_contract->id;

                    $contractor_contr_agent = ContrAgent::query()->firstOrCreate([
                        'uuid' => $contractor_data['contr_agent_id'],
                    ]);
                    $contractor_data['contr_agent_id'] = $contractor_contr_agent->id;

                    unset($provider_data['contr_agent_name']);
                    unset($provider_data['provider_contract_name']);

                    $order = Order::query()->where('uuid', $item['id'])->firstOr(
                    //Если обьект новый и его нужно создать
                        function () use ($item, $customer_data, $provider_data, $contractor_data) {
                            $customer = Customer::query()->create($customer_data);
                            $provider = Provider::query()->create($provider_data);
                            $contractor = Contractor::query()->create($contractor_data);

                            //TODO проверить number
                            return Order::withoutEvents(function () use ($item, $customer, $provider, $contractor) {
                                return Order::query()->create(
                                    [
                                        'uuid' => $item['id'],
                                        'number' => $item['number'] ?? null,
                                        'order_date' => (new Carbon($item['order_date']))->format('d.m.Y'),
                                        'deadline_date' => $item['deadline_date'] ?? null,
                                        'customer_status' => $item['customer_status'],
                                        'provider_status' => $item['provider_status'],
                                        'customer_id' => $customer->id,
                                        'provider_id' => $provider->id,
                                        'contractor_id' => $contractor->id,
                                    ]);
                            });
                        });

                    //Если обьект существует и его нужно обновить
                    if (!$order->wasRecentlyCreated) {
                        $order->customer()->update($customer_data);
                        $order->provider()->update($provider_data);
                        $order->contractor()->update($contractor_data);
                        $order->update([
                            'number' => $item['number'] ?? null,
                            'order_date' => (new Carbon($item['order_date']))->format('d.m.Y'),
                            'deadline_date' => $item['deadline_date'] ?? null,
                            'customer_status' => $item['customer_status'],
                            'provider_status' => $item['provider_status'],
                        ]);
                    }

                    $position_ids = [];
                    foreach ($position_data as $position) {
                        $nomenclature = Nomenclature::query()->firstOrCreate([
                            'uuid' => $position['nomenclature_id'],
                        ]);
//                        $nomenclature_unit = NomenclatureUnit::query()->where('uuid', $position['unit_id'])->firstOrFail();
                        $position = $order->positions()->updateOrCreate(['position_id' => $position['position_id']], [
                            'status' => $position['status'] ?? null,
                            'nomenclature_id' => $nomenclature->id,
                            #'unit_id' => $nomenclature_unit->id,
                            'count' => $position['count'] ?? null,
                            'price_without_vat' => $position['price_without_vat'] ?? null,
                            'amount_without_vat' => $position['amount_without_vat'] ?? null,
                            'delivery_time' => $position['delivery_time'] ?? null,
                            'delivery_address' => $position['delivery_address'] ?? null,
                        ]);
                        $position_ids[] = $position->id;
                    }
                    $order->positions()->whereNotIn('id', $position_ids)->delete();
                });
            }
            return response()->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function synchronize(Request $request)
    {
        try {
            return DB::transaction(function () {
                $orders = Order::query()
                    ->with(['customer.subObject', 'customer.object'])
                    /*->where('sync_required', true)*/ #todo: расскомментировать в будущем
                    ->get();
//                Order::query()->whereIn('id', $orders->pluck('id'))->update(['sync_required' => false]);#todo: расскомментировать в будущем
                return fractal()->collection($orders)->transformWith(OrderTransformer::class)->serializeWith(CustomerSerializer::class);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
