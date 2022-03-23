<?php


namespace App\Http\Controllers\Consignments;


use App\Http\Controllers\Controller;
use App\Models\Consignments\Consignment;
use App\Models\Orders\LKK\Order;
use App\Models\Provider;
use App\Models\References\ContrAgent;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Services\ConsignmentNotes\CreateConsignmentService;
use App\Services\ConsignmentNotes\GetConsignmentService;
use App\Services\ConsignmentNotes\GetConsignmentsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConsignmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $consignments = (new GetConsignmentsService($request->all()))->run();
            return response()->json($consignments);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function getConsignment(Request $request, $consignment_id)
    {


        try {
            $consignment = Consignment::query()->findOrFail($consignment_id);
            $consignment = (new GetConsignmentService($request->all(), $consignment))->run();
            return response()->json(['data' => $consignment]);
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

    public function create(Request $request)
    {
        Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'responsible_full_name' => 'required|string|max:255',
            'responsible_phone' => 'required|string|max:255',
            'comment' => 'required|string',
            'positions' => 'required|array',
            'positions.*' => 'required',
            'positions.*.nomenclature_id' => 'required|integer|exists:nomenclature,id',
            'positions.*.unit_id' => 'required|integer|exists:nomenclature_units,id',
            'positions.*.count' => 'required|numeric',
            'positions.*.price_without_vat' => 'required|numeric',
            //TODO отрефакторить ставку НДС
            'positions.*.vat_rate' => ['required', Rule::in([1, 1.13, 1.2, 1.3, 1.4])],
            'positions.*.country' => 'required|string',
            'positions.*.cargo_custom_declaration' => 'required|string',
            'positions.*.declaration' => 'required|string',
        ])->validate();
        try {
            $consignment = (new CreateConsignmentService($request->all()))->run();
            return response()->json(['data' => $consignment]);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

//    public function getOrganizations(Request $request)
//    {
//        $organizations = Organization::query()->paginate(15);
//        return response()->json($organizations);
//    }
//
//    public function getContrAgents(Request $request)
//    {
//        $contr_agents = ContrAgent::query()->paginate();
//        return response()->json($contr_agents);
//    }
//
//    public function getWorkAgreements(Request $request)
//    {
//        $work_agreements = WorkAgreementDocument::query()->paginate();
//        return response()->json($work_agreements);
//    }
//
//    public function getProviderContracts(Request $request)
//    {
//        $provider_contracts = ProviderContractDocument::query()->paginate();
//        return response()->json($provider_contracts);
//    }

    public function searchOrders(Request $request)
    {
        Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'provider_contr_agent_id' => 'required|exists:contr_agents,id',
            'provider_contract_id' => 'required|exists:provider_contracts,id',
            'work_agreement_id' => 'required|exists:work_agreements,id',
            'contractor_contr_agent_id' => 'required|exists:contr_agents,id',
        ])->validate();

        try {
            $orders = DB::table('orders')
                ->select(['orders.id as order_id', 'orders.number as order_number', 'customer_objects.name as object_name', 'customer_sub_objects.name as sub_object_name'])
                ->leftJoin('order_customers', 'order_customers.id', '=', 'orders.customer_id')
                ->leftJoin('customer_objects', 'customer_objects.id', '=', 'order_customers.object_id')
                ->leftJoin('customer_sub_objects', 'customer_sub_objects.id', '=', 'order_customers.sub_object_id')
                ->leftJoin('order_providers', 'order_providers.id', '=', 'orders.provider_id')
                ->leftJoin('order_contractors', 'order_contractors.id', '=', 'orders.contractor_id')
                ->where('order_customers.organization_id', $request->organization_id)
                ->where('order_customers.work_agreement_id', $request->work_agreement_id)
                ->where('order_providers.contr_agent_id', $request->provider_contr_agent_id)
                ->where('order_providers.provider_contract_id', $request->provider_contract_id)
                ->where('order_contractors.contr_agent_id', $request->contractor_contr_agent_id)
                ->get();

            return response()->json($orders);
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
