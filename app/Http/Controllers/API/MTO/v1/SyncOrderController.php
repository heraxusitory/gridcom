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
use App\Models\References\CustomerSubObject;
use App\Models\References\Nomenclature;
use App\Models\References\NomenclatureUnit;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SyncOrderController extends Controller
{
    public function pull(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required|array',
            'orders.*.id' => 'required|uuid',
            'orders.*.number' => 'required|string|max:255',
            'orders.*.deadline_date' => 'required|date_format:d.m.Y',
            'orders.*.customer_status' => Rule::in(Order::getCustomerStatuses()),
            'orders.*.provider_status' => Rule::in(Order::getProviderStatuses()),

            'orders.*.order_customer.organization_id' => 'required|uuid',
            'orders.*.order_customer.work_agreement_id' => 'required|uuid',
            'orders.*.order_customer.work_type' => 'required|string|max:255',
            'orders.*.order_customer.object_id' => 'required|uuid',
            'orders.*.order_customer.sub_object_id' => 'required|uuid',
            'orders.*.order_customer.work_start_date' => 'required|date_format:d.m.Y',
            'orders.*.order_customer.work_end_date' => 'required|date_format:d.m.Y',

            'orders.*.order_provider.provider_contract_id' => 'required|uuid',
//            'orders.order_provider.contact_id' => 'required|uuid|exists:contact_persons,uuid',
            'orders.*.order_provider.contr_agent_id' => 'required|uuid',
            'orders.*.order_provider.full_name' => 'required|string|max:255',
            'orders.*.order_provider.email' => 'required|string|max:255',
            'orders.*.order_provider.phone' => 'required|string|max:255',

//            'orders.order_contractor.contact_id' => 'required|uuid|exists:contact_persons,uuid',
            'orders.*.order_contractor.contr_agent_id' => 'required|uuid',
            'orders.*.order_contractor.full_name' => 'required|string|max:255',
            'orders.*.order_contractor.email' => 'required|string|max:255',
            'orders.*.order_contractor.phone' => 'required|string|max:255',
            'orders.*.order_contractor.contractor_responsible_full_name' => 'required|string|max:255',
            'orders.*.order_contractor.contractor_responsible_phone' => 'required|string|max:255',

            'orders.*.order_positions' => 'required|array',
            'orders.*.order_positions.*.position_id' => 'required|uuid',
            'orders.*.order_positions.*.status' => Rule::in(OrderPosition::getStatuses()),
            'orders.*.order_positions.*.nomenclature_id' => 'required|uuid',
//            'orders.*.order_positions.*.unit_id' => 'required|uuid|exists:nomenclature_units,uuid',
            'orders.*.order_positions.*.count' => 'required|numeric',
            'orders.*.order_positions.*.price_without_vat' => 'required|numeric',
            'orders.*.order_positions.*.amount_without_vat' => 'required|numeric',
            'orders.*.order_positions.*.delivery_time' => 'required|date_format:d.m.Y',
            'orders.*.order_positions.*.delivery_address' => 'required|string|max:255',
        ])->validate();

        try {
            $orders = $request->all()['orders'];

            foreach ($orders as $key => $order) {
                $object_id = $order['order_customer']['object_id'];
                $sub_object_id = $order['order_customer']['sub_object_id'];
                $customer_object = CustomerObject::query()->firstOrCreate([
                    'uuid' => $object_id,
                ]);
                $customer_sub_object = CustomerSubObject::query()->firstOrCreate([
                    'uuid' => $sub_object_id,
                ], [
                    'customer_object_id' => $customer_object->id,
                ]);
//                throw_if($customer_sub_object->customer_object_id !== $customer_object->id,
//                    new BadRequestException('The selected orders.' . $key . '.order_customer.sub_object_id is invalid', 422));
            }
        } catch (BadRequestException $e) {
            return response()->json($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json('System error', 500);
        }

        $data = $request->all()['orders'];

        try {
            foreach ($data as $item) {
                DB::transaction(function () use ($item) {
                    $customer_data = $item['order_customer'];
                    $provider_data = $item['order_provider'];
                    $contractor_data = $item['order_contractor'];
                    $position_data = $item['order_positions'];

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

                    $provider_contr_agent = ContrAgent::query()->firstOrCreate([
                        'uuid' => $provider_data['contr_agent_id'],
                    ]);
                    $provider_contract = ProviderContractDocument::query()->firstOrCreate([
                        'uuid' => $provider_data['provider_contract_id'],
                    ]);
                    $provider_data['contr_agent_id'] = $provider_contr_agent->id;
                    $provider_data['provider_contract_id'] = $provider_contract->id;

                    $contractor_contr_agent = ContrAgent::query()->firstOrCreate([
                        'uuid' => $contractor_data['contr_agent_id'],
                    ]);
                    $contractor_data['contr_agent_id'] = $contractor_contr_agent->id;

                    $order = Order::query()->where('uuid', $item['id'])->firstOr(
                    //Если обьект новый и его нужно создать
                        function () use ($item, $customer_data, $provider_data, $contractor_data) {
                            $customer = Customer::query()->create($customer_data);
                            $provider = Provider::query()->create($provider_data);
                            $contractor = Contractor::query()->create($contractor_data);

                            return Order::query()->create(
                                [
                                    'uuid' => $item['id'],
                                    'number' => $item['number'],
                                    'order_date' => (new Carbon($item['order_date']))->format('d.m.Y'),
                                    'deadline_date' => $item['deadline_date'],
                                    'customer_status' => $item['customer_status'],
                                    'provider_status' => $item['provider_status'],
                                    'customer_id' => $customer->id,
                                    'provider_id' => $provider->id,
                                    'contractor_id' => $contractor->id,
                                ]);
                        });

                    //Если обьект существует и его нужно обновить
                    if (!$order->wasRecentlyCreated) {
                        $order->customer()->update($customer_data);
                        $order->provider()->update($provider_data);
                        $order->contractor()->update($contractor_data);
                        $order->update([
                            'number' => $item['number'],
                            'order_date' => (new Carbon($item['order_date']))->format('d.m.Y'),
                            'deadline_date' => $item['deadline_date'],
                            'customer_status' => $item['customer_status'],
                            'provider_status' => $item['provider_status'],
                        ]);
                    }

                    foreach ($position_data as $position) {
                        $nomenclature = Nomenclature::query()->firstOrCreate([
                            'uuid' => $position['nomenclature_id'],
                        ]);
//                        $nomenclature_unit = NomenclatureUnit::query()->where('uuid', $position['unit_id'])->firstOrFail();
                        $order->positions()->updateOrCreate(['position_id' => $position['position_id']], [
                            'status' => $position['status'],
                            'nomenclature_id' => $nomenclature->id,
                            #'unit_id' => $nomenclature_unit->id,
                            'count' => $position['count'],
                            'price_without_vat' => $position['price_without_vat'],
                            'amount_without_vat' => $position['amount_without_vat'],
                            'delivery_time' => $position['delivery_time'],
                            'delivery_address' => $position['delivery_address'],
                        ]);
                    }
                });
            }
            return response()->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
