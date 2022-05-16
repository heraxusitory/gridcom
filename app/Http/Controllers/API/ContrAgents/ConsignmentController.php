<?php


namespace App\Http\Controllers\API\ContrAgents;


use App\Http\Controllers\Controller;
use App\Models\Consignments\Consignment;
use App\Models\IntegrationUser;
use App\Models\Provider;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Services\API\ContrAgents\v1\CreateOrUpdateConsignmentService;
use App\Transformers\API\ContrAgents\v1\ConsignmentTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConsignmentController extends Controller
{
    public function sync(Request $request)
    {
        $user = Auth::guard('api')->user();
        Validator::validate($request->all(), [
            'consignments' => 'required|array',
            'consignments.*.id' => 'required|uuid',
            'consignments.*.number' => 'required|string|max:255',
            'consignments.*.date' => 'required|date_format:Y-m-d',
            'consignments.*.organization.name' => ['required', 'string', 'max:255'],
            'consignments.*.provider_contr_agent.name' => 'required|string|max:255',
            'consignments.*.provider_contract.number' => 'required|string|max:255',
            'consignments.*.contractor_contr_agent.name' => 'required|string|max:255',
            'consignments.*.work_agreement.number' => 'required|string|max:255',
            'consignments.*.customer_object.name' => 'required|uuid',
            'consignments.*.customer_sub_object.name' => 'nullable|string|max:255',
//            'consignments.*.order_id' => 'required|uuid|exists:orders,uuid',
            'consignments.*.responsible_full_name' => 'nullable|string|max:255',
            'consignments.*.responsible_phone' => 'nullable|string|max:255',
            'consignments.*.comment' => 'nullable|string',

            'consignments.*.positions' => 'nullable|array',
            'consignments.*.positions.*.id' => 'required|uuid',
            'consignments.*.positions.*.order_id' => 'required|uuid',
            'consignments.*.positions.*.nomenclature.name' => 'required|string|max:255',
            'consignments.*.positions.*.nomenclature.mnemocode' => 'required|string|max:255',
            'consignments.*.positions.*.count' => 'required|numeric',
            'consignments.*.positions.*.price_without_vat' => 'required|numeric',
            'consignments.*.positions.*.amount_without_vat' => 'required|numeric',
            'consignments.*.positions.*.vat_rate' => 'required|numeric',
            'consignments.*.positions.*.amount_with_vat' => 'required|numeric',
            'consignments.*.positions.*.country' => ['required', 'string', Rule::in(array_keys(config('countries')))],
            'consignments.*.positions.*.cargo_custom_declaration' => 'nullable|string',
            'consignments.*.positions.*.declaration' => 'nullable|string',
        ]);

        try {
            $data = $request->all()['consignments'];
            (new CreateOrUpdateConsignmentService($data, $user))->run();
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
                    $consignments = ContractorSyncStack::getModelEntities(Consignment::class, $this->contr_agent);
                else if ($user->isProvider())
                    $consignments = ProviderSyncStack::getModelEntities(Consignment::class, $this->contr_agent);
                else $consignments = [];
                return fractal()->collection($consignments)->transformWith(ConsignmentTransformer::class)->serializeWith(CustomerSerializer::class);
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
                return response()->json('Из стека удалено ' . $count . ' накладных');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
