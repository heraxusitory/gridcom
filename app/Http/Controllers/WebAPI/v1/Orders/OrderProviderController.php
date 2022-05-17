<?php


namespace App\Http\Controllers\WebAPI\v1\Orders;


use App\Events\NewStack;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OrderProviderController extends OrderController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function approve(Request $request, $order_id)
    {
        Validator::make($request->all(), [
            'comment' => 'required|string',
        ])->validate();

        try {
            $order = Order::query();
            if ($this->user->isProvider()) {
                $order->whereRelation('provider', 'contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var Order $order */
            $order = $order->findOrFail($order_id);

            throw_if($order->provider_status === Order::PROVIDER_STATUS_AGREED ||
                $order->provider_status === Order::PROVIDER_STATUS_PARTIALLY_AGREED
                , new BadRequestException('Заказ уже согласован или частично согласован поставщиком', 400));
            throw_if($order->provider_status === Order::PROVIDER_STATUS_NOT_AGREED
                , new BadRequestException('Заказ уже отказан поставщиком', 400));

//            $order->positions()->where('status', '!=', OrderPosition::STATUS_REJECTED)->update(['status' => OrderPosition::STATUS_AGREED]);
            $order->positions()->whereNotIn('status', [OrderPosition::STATUS_REJECTED])->update(['status' => OrderPosition::STATUS_AGREED]);

            if ($order->positions()->where('status', OrderPosition::STATUS_REJECTED)->count())
                $order->provider_status = Order::PROVIDER_STATUS_PARTIALLY_AGREED;
            else
                $order->provider_status = Order::PROVIDER_STATUS_AGREED;
            $order_provider = $order->provider()->firstOrFail();
            $order_provider->agreed_comment = $request->comment;
            $order_provider->save();
            $order->save();

            event(new NewStack($order,
                    (new ContractorSyncStack())->setContractor($order->contractor->contr_agent),
                    new MTOSyncStack())
            );

            return $order;
        } catch
        (ModelNotFoundException $e) {
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

    public function reject(Request $request, $order_id)
    {
        Validator::make($request->all(), [
            'comment' => 'required|string',
        ])->validate();

        try {
            $order = Order::query();
            if ($this->user->isProvider()) {
                $order->whereRelation('provider', 'contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var Order $order */
            $order = $order->findOrFail($order_id);

            throw_if($order->provider_status === Order::PROVIDER_STATUS_AGREED ||
                $order->provider_status === Order::PROVIDER_STATUS_PARTIALLY_AGREED
                , new BadRequestException('Заказ уже согласован или частично согласован поставщиком', 400));
            throw_if($order->provider_status === Order::PROVIDER_STATUS_NOT_AGREED
                , new BadRequestException('Заказ уже отказан поставщиком', 400));

            $order->positions()->update(['status' => OrderPosition::STATUS_REJECTED]);
            $order->provider_status = Order::PROVIDER_STATUS_NOT_AGREED;
            $order_provider = $order->provider()->firstOrFail();
            $order_provider->rejected_comment = $request->comment;
            $order_provider->save();
            $order->save();

            event(new NewStack($order,
                    (new ContractorSyncStack())->setContractor($order->contractor->contr_agent),
                    new MTOSyncStack())
            );


            return $order;
        } catch
        (ModelNotFoundException $e) {
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

    public function rejectPositions(Request $request, $order_id)
    {
        try {
            $order = Order::query();
            if ($this->user->isProvider()) {
                $order->whereRelation('provider', 'contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var Order $order */
            $order = $order->findOrFail($order_id);
            $order_positions_ids = $order->positions()->pluck('id');
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
        Validator::make($request->all(), [
            'rejected_position_ids' => 'required|array',
            'rejected_position_ids.*' => ['required', 'integer', Rule::in($order_positions_ids)],
            'comment' => 'required|string',
        ])->validate();

        try {
            throw_if(count($order_positions_ids) === count($request->rejected_position_ids),
                new BadRequestException('Невозможно отказать сразу по всем выбранным позициям', 400));

            throw_if($order->provider_status === Order::PROVIDER_STATUS_AGREED ||
                $order->provider_status === Order::PROVIDER_STATUS_PARTIALLY_AGREED
                , new BadRequestException('Заказ уже согласован или частично согласован поставщиком', 400));
            throw_if($order->provider_status === Order::PROVIDER_STATUS_NOT_AGREED
                , new BadRequestException('Заказ уже отказан поставщиком', 400));

//            throw_if($order->positions()->where('status', OrderPosition::STATUS_REJECTED)->exists(), new BadRequestException('Отказанные позиции уже существуют', 400));
            $order->provider->rejected_comment = $request->comment;
            $order->positions()->update(['status' => OrderPosition::STATUS_UNDER_CONSIDERATION]);
            $order->positions()->whereIn('id', $request->rejected_position_ids)->update(['status' => OrderPosition::STATUS_REJECTED]);
            $order->push();

            event(new NewStack($order,
                    (new ContractorSyncStack())->setContractor($order->contractor->contr_agent),
                    new MTOSyncStack())
            );

            return $order;
        } catch
        (ModelNotFoundException $e) {
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

    public function changePosition(Request $request, $order_id, $order_position_id)
    {
        Validator::make($request->all(), [
            'delivery_plan_time' => 'nullable|date_format:Y-m-d',
            'comment' => 'nullable|string',
        ])->validate();

        try {
            $order = Order::query();
            if ($this->user->isProvider()) {
                $order->whereRelation('provider', 'contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var Order $order */
            $order = $order->findOrFail($order_id);
            $position = $order->positions()->findOrFail($order_position_id);
            $position->delivery_plan_time = $request->delivery_plan_time;
            $position->provider_comment = $request->comment;
            $position->save();

            event(new NewStack($order,
                    (new ContractorSyncStack())->setContractor($order->contractor->contr_agent),
                    new MTOSyncStack())
            );

            return response()->json(['data' => $position]);
        } catch
        (ModelNotFoundException $e) {
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

    public function approveClosing(Request $request, $order_id)
    {
        try {
            $order = Order::query();
            if ($this->user->isProvider()) {
                $order->whereRelation('provider', 'contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var Order $order */
            $order = $order->findOrFail($order_id);
            throw_if($order->customer_status !== Order::CUSTOMER_STATUS_AGREED && !in_array($order->provider_status, [Order::PROVIDER_STATUS_AGREED, Order::PROVIDER_STATUS_PARTIALLY_AGREED]),
                new BadRequestException('Невозможно завершить заказ на поставку. Требуется согласованные статусы со стороны заказчика и поставщика.', 400));
            throw_if(!$order->contractor_require_closure,
                new BadRequestException('Невозможно подтвердить завершение заказа. Требуется сначала запрос на завершение заказа со стороны подрядчика.', 400));
            $order->provider_closing_confirmation = true;
            $order->customer_status = Order::CUSTOMER_STATUS_CLOSED;
            $order->provider_status = Order::PROVIDER_STATUS_CLOSED;
            $order->push();
            return response()->json(['data' => $order]);
        } catch
        (ModelNotFoundException $e) {
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
