<?php


namespace App\Http\Controllers\Orders;


use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CreateFormRequest;
use App\Http\Requests\Orders\UpdateFormRequest;
use App\Models\Orders\Order;
use App\Services\Orders\CreateOrderService;
use App\Services\Orders\GetOrderService;
use App\Services\Orders\GetOrdersService;
use App\Services\Orders\UpdateOrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        try {
            $data = $request->all();
            $orders = (new GetOrdersService($data))->run();
            return response()->json(['data' => $orders]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function getOrder(Request $request, $order_id)
    {
        try {
            Order::query()->findOrFail($order_id);
            $order = (new GetOrderService($request->all(), $order_id))->run();
            return response()->json(['data' => $order]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function create(CreateFormRequest $request): JsonResponse
    {
        try {
            $order = (new CreateOrderService($request->all()))->run();
            return response()->json(['data' => $order], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function update(Request $request, $order_id): JsonResponse
    {
        /**
         * @var Order $order
         */
        try {
            $order = Order::query()->findOrFail($order_id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        Validator::make($request->all(), [
            'action' => ['required', Rule::in(['draft', 'approve'])],
            'order_date' => 'date_format:d:m:Y',
            'deadline_date' => 'date_format:d:m:Y',
            'customer.organization' => 'required|string|exists:organizations,name',
            'customer.work_agreement.number' => 'required|string|exists:work_agreements,number',
            'customer.work_agreement.date' => 'required|date_format:d:m:Y',
            'customer.work_type' => ['required', Rule::in(['Cтроительство', 'Разработка', 'Интеграция'])],
            'customer.object' => 'required|exists:customer_objects,name',
            'customer.sub_object' => 'required|exists:customer_sub_objects,name',

            'provider.name' => 'required|exists:contr_agents,name',
            'provider.contract.number' => 'required|exists:provider_contracts,number',
            'provider.contract.date' => 'required|exists:provider_contracts,date',
            'provider.contact.full_name' => 'required|string',
            'provider.contact.email' => 'required|string',
            'provider.contact.phone' => 'required|string',

            'contractor.name' => 'required|exists:contr_agents,name',
            'contractor.full_name' => 'required|string',
            'contractor.email' => 'required|string',
            'contractor.phone' => 'required|string',
            'contractor.responsible_full_name' => 'required|string',
            'contractor.responsible_phone' => 'required|string',
        ]);

        try {
            $order = (new UpdateOrderService($request->all(), $order))->run();
            return response()->json(['data' => $order], 201);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function delete(Request $request, $order_id)
    {
        try {
            DB::transaction(function () use ($order_id) {
                $order = Order::query()->findOrFail($order_id);
                $order->positions()->delete();
                $order->customer()->delete();
                $order->provider()->delete();
                $order->contractor()->delete();
                $order->delete();
            });
            return response(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
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
