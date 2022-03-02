<?php


namespace App\Http\Controllers\Orders;


use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CreateFormRequest;
use App\Models\Orders\Order;
use App\Services\Orders\CreateOrderService;
use App\Services\Orders\GetOrderService;
use App\Services\Orders\GetOrdersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $data = $request->all();
        $orders = (new GetOrdersService($data))->run();
        return response()->json(['data' => $orders]);
    }

    public function getOrder(Request $request, $order_id)
    {
        $order = Order::query()->findOrFail($order_id);
        $order = (new GetOrderService($request->all(), $order_id))->run();
        return response()->json(['data' => $order]);
    }

    public function create(CreateFormRequest $request): JsonResponse
    {
        try {
            $order = (new CreateOrderService($request->all()))->run();
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
}
