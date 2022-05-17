<?php


namespace App\Http\Controllers\API\ContrAgents;


use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\PaymentRegisters\PaymentRegisterPosition;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Services\API\ContrAgents\v1\CreateOrUpdatePaymentRegisterService;
use App\Transformers\API\ContrAgents\v1\PaymentRegisterTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PaymentRegisterController extends Controller
{
    public function sync(Request $request)
    {
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();
        $request->validate(
            [
                'payment_registers' => 'required|array',
                'payment_registers.*.id' => 'required|uuid',
                'payment_registers.*.number' => 'required|string|max:255',
//                'payment_registers.*.customer_status' => ['required', Rule::in(PaymentRegister::getCustomerStatuses())],
                'payment_registers.*.provider_status' => ['required', Rule::in(PaymentRegister::getProviderStatuses())],
                'payment_registers.*.provider_contr_agent.name' => ['required', 'string', 'max:255'],
                'payment_registers.*.contractor_contr_agent.name' => ['required', 'string', 'max:255'],
                'payment_registers.*.provider_contract.number' => ['required', 'string', 'max:255'],
                'payment_registers.*.responsible_full_name' => ['nullable', 'string', 'max:255'],
                'payment_registers.*.responsible_phone' => ['nullable', 'string', 'max:255'],
                'payment_registers.*.comment' => ['nullable', 'string'],
                'payment_registers.*.date' => ['required', 'date_format:Y-m-d'],

                'payment_registers.*.positions' => ['nullable', 'array'],
                'payment_registers.*.positions.*.position_id' => ['required', 'uuid'],
                'payment_registers.*.positions.*.order_id' => ['required', 'uuid'],
                'payment_registers.*.positions.*.payment_order_number' => ['nullable', 'string', 'max:255'],
                'payment_registers.*.positions.*.payment_order_date' => ['nullable', 'date_format:Y-m-d'],
                'payment_registers.*.positions.*.amount_payment' => ['nullable', 'numeric'],
                'payment_registers.*.positions.*.payment_type' => ['required', Rule::in(PaymentRegisterPosition::getPaymentTypes())],
            ]
        );

        try {
            $data = $request->all()['payment_registers'];
            (new CreateOrUpdatePaymentRegisterService($data, $user))->run();
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
                /** @var IntegrationUser $user */
                $user = Auth::guard('api')->user();
                if ($user->isContractor())
                    $pr = ContractorSyncStack::getModelEntities(PaymentRegister::class, $user->contr_agent);
                else if ($user->isProvider())
                    $pr = ProviderSyncStack::getModelEntities(PaymentRegister::class, $user->contr_agent);
                else $pr = [];
                return fractal()->collection($pr)->transformWith(PaymentRegisterTransformer::class)->serializeWith(CustomerSerializer::class);
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
                /** @var IntegrationUser $user */
                $user = Auth::guard('api')->user();
                $count = 0;
                if ($user->isProvider())
                    $count = ProviderSyncStack::destroy($request->stack_ids);
                elseif ($user->isContractor())
                    $count = ContractorSyncStack::destroy($request->stack_ids);
                return response()->json('Из стека удалено ' . $count . ' реестров платежей');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
