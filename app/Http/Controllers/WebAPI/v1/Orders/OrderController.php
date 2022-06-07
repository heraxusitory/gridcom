<?php


namespace App\Http\Controllers\WebAPI\v1\Orders;


use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Services\Filters\OrderFilter;
use App\Services\Orders\GetOrderService;
use App\Services\Orders\GetOrdersService;
use App\Services\Orders\Reports\GetReportService;
use App\Services\Sortings\OrderSorting;
use App\Transformers\WebAPI\v1\OrderTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function index(Request $request, OrderFilter $filter, OrderSorting $sorting)
    {
        try {
            $data = $request->all();
            $orders = (new GetOrdersService($data, $filter, $sorting))->run();
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
            $order = Order::query();
            if ($this->user->isProvider()) {
                $order->whereRelation('provider', 'contr_agent_id', $this->user->contr_agent_id())
                    ->whereRelation('provider', 'provider_status', '<>', Order::PROVIDER_STATUS_DRAFT);
            } elseif ($this->user->isContractor()) {
                $order->whereRelation('contractor', 'contr_agent_id', $this->user->contr_agent_id());
            }
            $order->findOrFail($order_id);
            $order = (new GetOrderService($request->all(), $order_id))->run();
            return fractal()->item($order)->transformWith(OrderTransformer::class)/*response()->json(['data' => $order])*/ ;
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
//        try {
        $order = Order::query();
        if ($this->user->isProvider()) {
            $order->whereRelation('provider', 'contr_agent_id', $this->user->contr_agent_id());
        } elseif ($this->user->isContractor()) {
            $order->whereRelation('contractor', 'contr_agent_id', $this->user->contr_agent_id());
        }
        /** @var Order $order */
        $order = $order->findOrFail($order_id);
        return response()->json(['data' => (new GetReportService($order))->run()]);
//        } catch (ModelNotFoundException $e) {
//            return response()->json(['message' => $e->getMessage()], 404);
//        } catch (\Exception $e) {
//            if ($e->getCode() >= 400 && $e->getCode() < 500)
//                return response()->json(['message' => $e->getMessage()], $e->getCode());
//            else {
//                Log::error($e->getMessage(), $e->getTrace());
//                return response()->json(['message' => 'System error'], 500);
//            }
//        }
    }
}
