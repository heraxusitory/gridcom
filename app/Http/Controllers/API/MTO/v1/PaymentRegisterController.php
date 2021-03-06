<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\PaymentRegisters\PaymentRegisterPosition;
use App\Models\References\ContrAgent;
use App\Models\References\ProviderContractDocument;
use App\Models\SyncStacks\MTOSyncStack;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\OrderTransformer;
use App\Transformers\API\MTO\v1\PaymentRegisterTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PaymentRegisterController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate(
            [
                'payment_registers' => 'required|array',
                'payment_registers.*.id' => 'required|uuid',
                'payment_registers.*.number' => 'required|string|max:255',
//                'payment_registers.*.customer_status' => ['required', Rule::in(PaymentRegister::getCustomerStatuses())],
                'payment_registers.*.provider_status' => ['required', Rule::in(PaymentRegister::getProviderStatuses())],
                'payment_registers.*.provider_contr_agent_id' => ['required', 'uuid'],
                'payment_registers.*.contractor_contr_agent_id' => ['required', 'uuid'],
                'payment_registers.*.provider_contract_id' => ['required', 'uuid'],
                'payment_registers.*.responsible_full_name' => ['nullable', 'string', 'max:255'],
                'payment_registers.*.responsible_phone' => ['nullable', 'string', 'max:255'],
                'payment_registers.*.comment' => ['nullable', 'string'],
                'payment_registers.*.date' => ['required', 'date_format:Y-m-d'],

                'payment_registers.*.positions' => ['nullable', 'array'],
                'payment_registers.*.positions.*.position_id' => ['required', 'uuid'],
                'payment_registers.*.positions.*.order_id' => ['required', 'uuid', 'exists:orders,uuid'],
                'payment_registers.*.positions.*.payment_order_number' => ['nullable', 'string', 'max:255'],
                'payment_registers.*.positions.*.payment_order_date' => ['nullable', 'date_format:Y-m-d'],
                'payment_registers.*.positions.*.amount_payment' => ['nullable', 'numeric'],
                'payment_registers.*.positions.*.payment_type' => ['required', Rule::in(PaymentRegisterPosition::getPaymentTypes())],
            ]
        );

        try {
            $data = $request->all()['payment_registers'];
            foreach ($data as $item) {
                DB::transaction(function () use ($item) {
                    $position_data = $item['positions'] ?? [];

//                    $payment_register = PaymentRegister::withoutEvents(function () use ($item) {
                    $provider_contr_agent = ContrAgent::query()->firstOrCreate([
                        'uuid' => $item['provider_contr_agent_id'],
                    ]);
                    $contractor_contr_agent = ContrAgent::query()->firstOrCreate([
                        'uuid' => $item['contractor_contr_agent_id'],
                    ]);
                    $provider_contract = ProviderContractDocument::query()->firstOrCreate([
                        'uuid' => $item['provider_contract_id'],
                    ]);

                    $payment_register = PaymentRegister::query()->updateOrCreate([
                        'uuid' => $item['id']
                    ], [
                        'number' => $item['number'],
//                            'customer_status' => $item['customer_status'],
                        'provider_status' => $item['provider_status'],
                        'provider_contr_agent_id' => $provider_contr_agent->id,
                        'provider_contract_id' => $provider_contract->id,
                        'contractor_contr_agent_id' => $contractor_contr_agent->id,
                        'responsible_full_name' => $item['responsible_full_name'],
                        'responsible_phone' => $item['responsible_phone'],
                        'comment' => $item['comment'],
                        'date' => (new Carbon($item['date']))->format('d.m.Y'),
                    ]);
//                    });

                    $position_ids = [];
                    foreach ($position_data as $position) {
                        $order = Order::query()->where('uuid', $position['order_id'])->firstOrFail();

                        $position = $payment_register->positions()->updateOrCreate([
                            'position_id' => $position['position_id'],
                        ], [
                            'payment_order_number' => $position['payment_order_number'] ?? null,
                            'order_id' => $order->id,
                            'payment_order_date' => $position['payment_order_date'] ?? null,
                            'amount_payment' => $position['amount_payment'] ?? null,
                            'payment_type' => $position['payment_type'],
                        ]);
                        $position_ids[] = $position->id;
                    }
                    $payment_register->positions()->whereNotIn('id', $position_ids)->delete();
                });
            }
            return response()->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function synchronize(Request $request)
    {
        try {
            return DB::transaction(function () {
                $orders = MTOSyncStack::getModelEntities(PaymentRegister::class);
                return fractal()->collection($orders)->transformWith(PaymentRegisterTransformer::class)->serializeWith(CustomerSerializer::class);
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
                return response()->json('???? ?????????? ?????????????? ' . $count . ' ???????????????? ????????????????');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
