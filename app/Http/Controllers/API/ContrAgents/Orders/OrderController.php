<?php


namespace App\Http\Controllers\API\ContrAgents\Orders;


use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Services\API\ContrAgents\v1\CreateOrUpdateOrderService;
use App\Transformers\API\ContrAgents\v1\OrderTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function sync(Request $request)
    {
        $user = Auth::guard('api')->user();
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|uuid',
            'orders.*.number' => 'nullable|string|max:255',
            'orders.*.deadline_date' => 'nullable|date_format:Y-m-d',
            'orders.*.order_date' => 'nullable|date_format:Y-m-d',
//            'orders.*.customer_status' => [Rule::in(Order::getCustomerStatuses())],
//            'orders.*.provider_status' => [Rule::in(Order::getProviderStatuses())],

            'orders.*.order_customer.organization.name' => 'required|string|max:255',
            'orders.*.order_customer.work_agreement.number' => 'required|string|max:255',
            'orders.*.order_customer.work_type' => 'required|string|max:255',
            'orders.*.order_customer.object.name' => 'required|string|max:255',
            'orders.*.order_customer.sub_object.name' => 'nullable|string|max:255',
            'orders.*.order_customer.work_start_date' => 'required|date_format:Y-m-d',
            'orders.*.order_customer.work_end_date' => 'required|date_format:Y-m-d',

            'orders.*.order_provider.provider_contract.number' => 'required|string|max:255',
            'orders.*.order_provider.contr_agent.name' => 'nullable|string|max:255',
            'orders.*.order_provider.full_name' => 'nullable|string|max:255',
            'orders.*.order_provider.email' => 'nullable|string|max:255',
            'orders.*.order_provider.phone' => 'nullable|string|max:255',

//            'orders.*.order_contractor.contr_agent.name' => 'required|string|max:255',
//            'orders.*.order_contractor.full_name' => 'nullable|string|max:255',
            'orders.*.order_contractor.email' => 'nullable|string|max:255',
            'orders.*.order_contractor.phone' => 'nullable|string|max:255',
            'orders.*.order_contractor.contractor_responsible_full_name' => 'nullable|string|max:255',
            'orders.*.order_contractor.contractor_responsible_phone' => 'nullable|string|max:255',

            'orders.*.order_positions' => 'nullable|array',
            'orders.*.order_positions.*.position_id' => 'required|uuid',
            'orders.*.order_positions.*.status' => ['nullable', Rule::in(OrderPosition::getStatuses())],
            'orders.*.order_positions.*.nomenclature.id' => 'required|uuid',
            'orders.*.order_positions.*.nomenclature.mnemocode' => 'required|string|max:255',
            'orders.*.order_positions.*.nomenclature.name' => 'required|string|max:255',
            'orders.*.order_positions.*.count' => 'nullable|numeric',
            'orders.*.order_positions.*.price_without_vat' => 'nullable|numeric',
            'orders.*.order_positions.*.amount_without_vat' => 'nullable|numeric',
            'orders.*.order_positions.*.delivery_time' => 'nullable|date_format:Y-m-d',
            'orders.*.order_positions.*.delivery_address' => 'nullable|string|max:255',
        ]);

        $data = $request->all()['orders'];

//        try {
            (new CreateOrUpdateOrderService($data, $user))->run();
            return response()->json();
//        } catch (\Exception $e) {
//            Log::error($e->getMessage(), $e->getTrace());
//            return response()->json(['message' => 'System error'], 500);
//        }
    }

    public function synchronize(Request $request)
    {
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();
        try {
            return DB::transaction(function () use ($user) {
                if ($user->isProvider())
                    $orders = ProviderSyncStack::getModelEntities(Order::class, $user->contr_agent);
                elseif ($user->isContractor())
                    $orders = ContractorSyncStack::getModelEntities(Order::class, $user->contr_agent);
                else $orders = [];
                return fractal()->collection($orders)->transformWith(OrderTransformer::class)->serializeWith(CustomerSerializer::class);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function removeFromStack(Request $request)
    {
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();
        $request->validate([
            'stack_ids' => 'required|array',
            'stack_ids.*' => 'required|uuid',
        ]);
        try {
            return DB::transaction(function () use ($request, $user) {
                $count = 0;
                if ($user->isProvider())
                    $count = ProviderSyncStack::destroy($request->stack_ids);
                elseif ($user->isContractor())
                    $count = ContractorSyncStack::destroy($request->stack_ids);
                return response()->json('Из стека удалено ' . $count . ' заказов на поставку ПО');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
