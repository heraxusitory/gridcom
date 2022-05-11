<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Events\NewStack;
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
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Serializers\CustomerSerializer;
use App\Services\API\MTO\v1\CreateOrUpdateOrderService;
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
            'orders.*.order_date' => 'nullable|date_format:Y-m-d',
            'orders.*.customer_status' => [Rule::in(Order::getCustomerStatuses())],
            'orders.*.provider_status' => [Rule::in(Order::getProviderStatuses())],

            'orders.*.order_customer.organization_id' => 'required|uuid',
            'orders.*.order_customer.work_agreement_id' => 'required|uuid',
            'orders.*.order_customer.work_type' => 'required|string|max:255',
            'orders.*.order_customer.object_id' => 'required|uuid',
            'orders.*.order_customer.sub_object_id' => 'nullable|uuid',
            'orders.*.order_customer.work_start_date' => 'nullable|date_format:Y-m-d',
            'orders.*.order_customer.work_end_date' => 'nullable|date_format:Y-m-d',

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
            'orders.*.order_positions.*.customer_comment' => 'nullable|string|max:255',
        ])->validate();

        $data = $request->all()['orders'];

//        try {
        (new CreateOrUpdateOrderService($data))->run();
        return response()->json();
//        } catch (\Exception $e) {
//            Log::error($e->getMessage(), $e->getTrace());
//            return response()->json(['message' => 'System error'], 500);
//        }
    }

    public function synchronize(Request $request)
    {
        try {
            return DB::transaction(function () {
                $orders = MTOSyncStack::query()
                    ->where('model', Order::class)
                    ->with('entity as order')->get()
                    ->map(function ($stack) {
                        $stack->order->stack_id = $stack->id;
                        return $stack->order;
                    });
//                $orders = Order::query()
                /*->with([
                    'customer.subObject', 'customer.object',
                    'provider.contract', 'provider.contr_agent',
                    'contractor.contr_agent',
                    'positions.nomenclature.units',
                ])*/
                /*->where('sync_required', true)*/ #todo: расскомментировать в будущем
//                    ->get();
//                Order::query()->whereIn('id', $orders->pluck('id'))->update(['sync_required' => false]);#todo: расскомментировать в будущем
                return fractal()->collection($orders)->transformWith(OrderTransformer::class)->serializeWith(CustomerSerializer::class);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function removeFromStack(Request $request)
    {
        $request->validate([
            'stack_ids' => 'required|array',
            'stack_ids.*' => 'required|uuid',
        ]);
        try {
            return DB::transaction(function () use ($request) {
                $count = MTOSyncStack::destroy($request->stack_ids);
//                $count = Order::withoutEvents(function () use ($request) {
//                    return Order::query()
//                        ->whereIn('uuid', $request->ids)
//                        ->update(['sync_required' => true]);
//                });
                return response()->json('Из стека удалено ' . $count . ' заказов на поставку ПО');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
