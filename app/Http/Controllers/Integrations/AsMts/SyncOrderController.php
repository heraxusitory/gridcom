<?php


namespace App\Http\Controllers\Integrations\AsMts;


use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\AbstractOrder;
use App\Models\Orders\Integrations\Order;
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

            'orders.*.order_customer.organization_id' => 'required|uuid|exists:organizations,uuid',
            'orders.*.order_customer.work_agreement_id' => 'required|uuid|exists:work_agreements,uuid',
            'orders.*.order_customer.work_type' => 'required|string|max:255',
            'orders.*.order_customer.object_id' => 'required|uuid|exists:customer_objects,uuid',
            'orders.*.order_customer.sub_object_id' => 'required|uuid|exists:customer_sub_objects,uuid',
            'orders.*.order_customer.work_start_date' => 'required|date_format:d.m.Y',
            'orders.*.order_customer.work_end_date' => 'required|date_format:d.m.Y',

            'orders.*.order_provider.provider_contract_id' => 'required|uuid|exists:provider_contracts,uuid',
//            'orders.order_provider.contact_id' => 'required|uuid|exists:contact_persons,uuid',
            'orders.*.order_provider.contr_agent_id' => 'required|uuid|exists:contr_agents,uuid',
            'orders.*.order_provider.full_name' => 'required|string|max:255',
            'orders.*.order_provider.email' => 'required|string|max:255',
            'orders.*.order_provider.phone' => 'required|string|max:255',

//            'orders.order_contractor.contact_id' => 'required|uuid|exists:contact_persons,uuid',
            'orders.*.order_contractor.contr_agent_id' => 'required|uuid|exists:contr_agents,uuid',
            'orders.*.order_contractor.full_name' => 'required|string|max:255',
            'orders.*.order_contractor.email' => 'required|string|max:255',
            'orders.*.order_contractor.phone' => 'required|string|max:255',
            'orders.*.order_contractor.contractor_responsible_full_name' => 'required|string|max:255',
            'orders.*.order_contractor.contractor_responsible_phone' => 'required|string|max:255',

            'orders.*.order_positions' => 'required|array',
            'orders.*.order_positions.*.position_id' => 'required|uuid',
            'orders.*.order_positions.*.status' => Rule::in(OrderPosition::getStatuses()),
            'orders.*.order_positions.*.nomenclature_id' => 'required|uuid|exists:nomenclature,uuid',
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
                $customer_sub_object = CustomerSubObject::query()->find($sub_object_id);
                throw_if($customer_sub_object->customer_object_id !== $object_id,
                    new BadRequestException('The selected orders.' . $key . '.order_customer.sub_object_id is invalid', 422));
            }
        } catch (BadRequestException $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

        $data = $request->all()['orders'];

        try {
            foreach ($data as $item) {
                DB::transaction(function () use ($item) {
                    $customer_data = $item['order_customer'];
                    $provider_data = $item['order_provider'];
                    $contractor_data = $item['order_contractor'];
                    $position_data = $item['order_positions'];

                    $organization = Organization::query()->where('uuid', $customer_data['organization_id'])->firstOrFail();
                    $work_agreement = WorkAgreementDocument::query()->where('uuid', $customer_data['work_agreement_id'])->firstOrFail();
                    $customer_object = CustomerObject::query()->where('uuid', $customer_data['object_id'])->firstOrFail();
                    $customer_sub_object = $customer_object->subObjects()->where('uuid', $customer_data['sub_object_id'])->firstOrFail();
                    $customer_data['organization_id'] = $organization->id;
                    $customer_data['work_agreement_id'] = $work_agreement->id;
                    $customer_data['object_id'] = $customer_object->id;
                    $customer_data['sub_object_id'] = $customer_sub_object->id;

                    $provider_contr_agent = ContrAgent::query()->where('uuid', $provider_data['contr_agent_id'])->firstOrFail();
                    $provider_contract = ProviderContractDocument::query()->where('uuid', $provider_data['provider_contract_id'])->firstOrFail();
                    $provider_data['contr_agent_id'] = $provider_contr_agent->id;
                    $provider_data['provider_contract_id'] = $provider_contract->id;

                    $contractor_contr_agent = ContrAgent::query()->where('uuid', $contractor_data['contr_agent_id'])->firstOrFail();
                    $contractor_data['contr_agent_id'] = $contractor_contr_agent->id;
//                    $customer = Customer::fill($customer_data);
//                    $provider = Provider::fill($provider_data);
//                    $contractor = Contractor::fill($contractor_data);

                    $order = Order::query()->where(['uuid' => $item['id'], 'number' => $item['number']])->firstOr(
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

                    if (!$order->wasRecentlyCreated) {
                        $order->customer()->update($customer_data);
                        $order->provider()->update($provider_data);
                        $order->contractor()->update($contractor_data);
                    }

                    foreach ($position_data as $position) {
                        $nomenclature = Nomenclature::query()->where('uuid', $position['nomenclature_id'])->firstOrFail();
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
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }
}
