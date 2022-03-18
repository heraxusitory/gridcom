<?php


namespace App\Http\Controllers\Orders;


use App\Http\Controllers\Controller;
use App\Models\Orders\LKK\Order;
use App\Services\Orders\GetOrderService;
use App\Services\Orders\GetOrdersService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
}
