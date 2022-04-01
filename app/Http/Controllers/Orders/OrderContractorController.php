<?php


namespace App\Http\Controllers\Orders;


use App\Http\Requests\Orders\CreateFormRequest;
use App\Models\Orders\LKK\Order;
use App\Services\Orders\CreateOrderService;
use App\Services\Orders\UpdateOrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OrderContractorController extends OrderController
{
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
            throw_if($order->customer_status !== Order::CUSTOMER_STATUS_DRAFT &&
                $order->provider_status !== Order::PROVIDER_STATUS_DRAFT,
                new BadRequestException('Невозможно редактировать заказ на поставку. Заказ отправлен на согласование', 400));
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

        Validator::make($request->all(), [
            'action' => ['required', Rule::in(['draft', 'approve'])],
            'order_date' => 'date_format:d.m.Y',
            'deadline_date' => 'date_format:d.m.Y',
            'customer.organization_id' => 'required|string|exists:organizations,id',
            'customer.work_agreement_id' => 'required|string|exists:work_agreements,id',
//            'customer.work_agreement.date' => 'required|date_format:d:m:Y',
            'customer.work_type' => ['required', Rule::in(['Строительство', 'Разработка', 'Интеграция'])],
            'customer.object_id' => 'required|exists:customer_objects,id',
            'customer.sub_object_id' => 'required|exists:customer_sub_objects,id',

            'provider.contr_agent_id' => 'required|exists:contr_agents,id',
//            'provider.contract.number' => 'required|exists:provider_contracts,number',
//            'provider.contract.date' => 'required|exists:provider_contracts,date',
            'provider.contract_id' => 'required|exists:provider_contracts,id',
            'provider.contact.full_name' => 'required|string',
            'provider.contact.email' => 'required|string',
            'provider.contact.phone' => 'required|string',

//            'contractor.name' => 'required|exists:contr_agents,name',
            'contractor.contr_agent_id' => 'required|exists:contr_agents,id',
            'contractor.contact.full_name' => 'required|string',
            'contractor.contact.email' => 'required|string',
            'contractor.contact.phone' => 'required|string',
            'contractor.responsible_full_name' => 'required|string',
            'contractor.responsible_phone' => 'required|string',

            'positions' => 'required_if:action,approve|array',
            'positions.*.nomenclature_id' => 'required|exists:nomenclature,id',
            'positions.*.count' => 'required|integer',
//            'positions.*.price_without_vat' => 'required|numeric',
            'positions.*.delivery_time' => 'required|date_format:d.m.Y',
            'positions.*.delivery_address' => 'required|string',
        ])->validate();

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
