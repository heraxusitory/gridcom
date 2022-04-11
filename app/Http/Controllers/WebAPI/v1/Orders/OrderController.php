<?php


namespace App\Http\Controllers\WebAPI\v1\Orders;


use App\Http\Controllers\Controller;
use App\Models\Orders\LKK\Order;
use App\Services\Orders\GetOrderService;
use App\Services\Orders\GetOrdersService;
use App\Services\Orders\Reports\GetReportService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $user = Auth::user();
            $order = Order::query();
            if ($user->isProvider()) {
                $order->whereRelation('provider', 'contr_agent_id', $user->contr_agent_id());
            } elseif ($user->isContractor()) {
                $order->whereRelation('contractor', 'contr_agent_id', $user->contr_agent_id());
            }
            $order->findOrFail($order_id);
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

    public function getReport(Request $request, $order_id)
    {
        $user = Auth::user();
        $order = Order::query();
        if ($user->isProvider()) {
            $order->whereRelation('provider', 'contr_agent_id', $user->contr_agent_id());
        } elseif ($user->isContractor()) {
            $order->whereRelation('contractor', 'contr_agent_id', $user->contr_agent_id());
        }
        /** @var Order $order */
        $order = $order->findOrFail($order_id);
        return response()->json(['data' => (new GetReportService($order))->run()]);
    }
}
