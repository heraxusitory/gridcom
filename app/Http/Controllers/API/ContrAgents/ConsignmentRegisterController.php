<?php


namespace App\Http\Controllers\API\ContrAgents;


use App\Http\Controllers\Controller;
use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\IntegrationUser;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Services\API\ContrAgents\v1\CreateOrUpdateConsignmentRegisterService;
use App\Transformers\API\ContrAgents\v1\ConsignmentRegisterTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConsignmentRegisterController extends Controller

{
    public function sync(Request $request)
    {
        $user = Auth::guard('api')->user();
        Validator::make($request->all(), [
            'consignment_registers' => 'required|array',
            'consignment_registers.*.id' => 'required|uuid',
            'consignment_registers.*.number' => 'required|string|max:255',
            'consignment_registers.*.customer_status' => ['required', Rule::in(ConsignmentRegister::getCustomerStatuses())],
            'consignment_registers.*.contr_agent_status' => ['required', Rule::in(ConsignmentRegister::getContrAgentStatuses())],

            'consignment_registers.*.organization.name' => 'required|string|max:255',
            'consignment_registers.*.contractor_contr_agent.name' => 'required|string|max:255',
            'consignment_registers.*.provider_contr_agent.name' => 'required|string|max:255',
            'consignment_registers.*.customer_object.name' => 'required|string|max:255',
            'consignment_registers.*.customer_sub_object.name' => 'required|string|max:255',
            'consignment_registers.*.work_agreement.number' => 'required|string|max:255',

            'consignment_registers.*.responsible_full_name' => 'nullable|string|max:255',
            'consignment_registers.*.responsible_phone' => 'nullable|string|max:255',
            'consignment_registers.*.comment' => 'nullable|string',
            'consignment_registers.*.date' => 'required|date_format:Y-m-d',

            'consignment_registers.*.positions' => 'nullable|array',
            'consignment_registers.*.positions.*.id' => 'required|uuid',
            'consignment_registers.*.positions.*.consignment_id' => 'required|uuid',
            'consignment_registers.*.positions.*.nomenclature.name' => 'required|string|max:255',
            'consignment_registers.*.positions.*.nomenclature.mnemocode' => 'required|string|max:255',
            'consignment_registers.*.positions.*.count' => 'required|numeric',
            'consignment_registers.*.positions.*.vat_rate' => 'required|numeric',
            'consignment_registers.*.positions.*.result_status' => 'required|string|max:255',
        ])->validate();

        try {
            $data = $request->all()['consignment_registers'];
            (new CreateOrUpdateConsignmentRegisterService($data, $user))->run();
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
                    $cr = ContractorSyncStack::getModelEntities(ConsignmentRegister::class, $user->contr_agent);
                else if ($user->isProvider())
                    $cr = ProviderSyncStack::getModelEntities(ConsignmentRegister::class, $user->contr_agent);
                else $cr = [];
                return fractal()->collection($cr)->transformWith(ConsignmentRegisterTransformer::class)->serializeWith(CustomerSerializer::class);
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
                return response()->json('Из стека удалено ' . $count . ' реестров товарных накладных');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
