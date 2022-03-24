<?php


namespace App\Http\Controllers\Consignments;


use App\Http\Controllers\Controller;
use App\Http\Requests\Consignments\CreateConsignmentFormRequest;
use App\Http\Requests\Consignments\UpdateConsignmentFormRequest;
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
use App\Services\ConsignmentNotes\UpdateConsignmentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

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

    public function create(CreateConsignmentFormRequest $request)
    {
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

    public function update(UpdateConsignmentFormRequest $request, $consignment_id)
    {
        try {
            /* @var Consignment $consignment */
            $consignment = Consignment::query()->findOrFail($consignment_id);
            throw_if($consignment->is_approved,
                new BadRequestException('Невозможно редактировать накладную. Накладная отправлена на согласование', 400));

            $consignment = (new UpdateConsignmentService($request->all(), $consignment))->run();
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

    public function delete(Request $request, $consignment_id)
    {
        try {
            $consignment = Consignment::query()->findOrFail($consignment_id);
            DB::transaction(function () use ($consignment) {
                $consignment->positions()->delete();
                $consignment->delete();
            });
            return response()->json('', 204);
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
