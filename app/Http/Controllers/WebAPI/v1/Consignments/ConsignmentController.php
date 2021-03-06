<?php


namespace App\Http\Controllers\WebAPI\v1\Consignments;


use App\Http\Controllers\Controller;
use App\Http\Requests\Consignments\CreateConsignmentFormRequest;
use App\Http\Requests\Consignments\UpdateConsignmentFormRequest;
use App\Models\Consignments\Consignment;
use App\Models\Consignments\ConsignmentPosition;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Serializers\CustomerSerializer;
use App\Services\Consignments\CreateConsignmentService;
use App\Services\Consignments\GetConsignmentService;
use App\Services\Consignments\GetConsignmentsService;
use App\Services\Consignments\UpdateConsignmentService;
use App\Services\Filters\ConsignmentFilter;
use App\Services\Filters\ConsignmentPositionFilter;
use App\Services\Sortings\ConsignmentPositionSorting;
use App\Services\Sortings\ConsignmentSorting;
use App\Transformers\WebAPI\v1\ConsignmentTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Serializer\ArraySerializer;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ConsignmentController extends Controller
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function index(Request $request, ConsignmentFilter $filter, ConsignmentSorting $sorting)
    {
        try {
            $consignments = (new GetConsignmentsService($request, $filter, $sorting))->run();
//            return fractal()->collection($consignments)->transformWith(ConsignmentTransformer::class)
//                ->serializeWith(new ArraySerializer())
//                ->paginateWith(new IlluminatePaginatorAdapter($consignments));
//                ->toArray()*//*->serializeWith(CustomerSerializer::class)*/ ;
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
            $consignment = Consignment::query();
            if ($this->user->isProvider()) {
                $consignment->where('provider_contr_agent_id', $this->user->contr_agent_id());
            } elseif ($this->user->isContractor()) {
                $consignment->where('contractor_contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var Consignment $consignment */
            $consignment = $consignment->findOrFail($consignment_id);
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

    public function getConsignmentPositions(Request $request, $consignment_id, ConsignmentPositionFilter $filter, ConsignmentPositionSorting $sorting)
    {
        try {
            $consignment = Consignment::query();
            if ($this->user->isProvider()) {
                $consignment->where('provider_contr_agent_id', $this->user->contr_agent_id());
            } elseif ($this->user->isContractor()) {
                $consignment->where('contractor_contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var Consignment $consignment */
            $consignment = $consignment->findOrFail($consignment_id);
            return response()->json(['data' => $consignment->positions()->filter($filter)->sorting($sorting)->paginate($request->per_page)]);
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

    public function update(UpdateConsignmentFormRequest $request, $consignment_id)
    {
        try {
            /* @var Consignment $consignment */
            $consignment = Consignment::query()->findOrFail($consignment_id);
            throw_if($consignment->is_approved,
                new BadRequestException('???????????????????? ?????????????????????????? ??????????????????. ?????????????????? ???????????????????? ???? ????????????????????????', 400));

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
            'customer_object_id' => 'required|exists:customer_objects,id',
            'customer_sub_object_id' => 'nullable|integer|exists:customer_sub_objects,id',
        ])->validate();

        try {
//            $orders = Order::query()
//                ->select(['orders.id as order_id', 'orders.number as order_number', 'customer_objects.name as object_name', 'customer_sub_objects.name as sub_object_name'])
//                ->leftJoin('order_customers', 'order_customers.id', '=', 'orders.customer_id')
//                ->leftJoin('customer_objects', 'customer_objects.id', '=', 'order_customers.object_id')
//                ->leftJoin('customer_sub_objects', 'customer_sub_objects.id', '=', 'order_customers.sub_object_id')
//                ->leftJoin('order_providers', 'order_providers.id', '=', 'orders.provider_id')
//                ->leftJoin('order_contractors', 'order_contractors.id', '=', 'orders.contractor_id')
//                ->where('order_customers.organization_id', $request->organization_id)
//                ->where('order_customers.work_agreement_id', $request->work_agreement_id)
//                ->where('order_providers.contr_agent_id', $request->provider_contr_agent_id)
//                ->where('order_providers.provider_contract_id', $request->provider_contract_id)
//                ->where('order_contractors.contr_agent_id', $request->contractor_contr_agent_id)
////                ->with(['positions'])
//                ->get();

            $orders = Order::query()
                ->whereRelation('customer', 'organization_id', $request->organization_id)
                ->whereRelation('customer', 'work_agreement_id', $request->work_agreement_id)
                ->whereRelation('customer', 'object_id', $request->customer_object_id)
                ->whereRelation('provider', 'contr_agent_id', $request->provider_contr_agent_id)
                ->whereRelation('provider', 'provider_contract_id', $request->provider_contract_id)
                ->whereRelation('contractor', 'contr_agent_id', $request->contractor_contr_agent_id)
                ->where('customer_status', '<>', Order::CUSTOMER_STATUS_DRAFT)
                ->where('provider_status', '<>', Order::PROVIDER_STATUS_DRAFT)
                ->with([
                    'customer', 'provider', 'contractor',
//                    'positions.nomenclature',
                    'positions' => function ($query) {
                        $query->where('status', OrderPosition::STATUS_AGREED)->with('nomenclature');
                    }
                ]);

            if ($request->customer_sub_object_id ?? null) {
                $orders = $orders->whereRelation('customer', 'sub_object_id', $request->customer_sub_object_id);
            }
            $orders = $orders/*->with('positions')*/ ->get();

            $orders = $orders->map(function ($order) {
                $order->positions->map(function ($position) use ($order) {
                    $max_available_count_in_order_position = (float)$position->count;
                    $common_count_by_consignments = (float)ConsignmentPosition::query()
                        ->where(['order_id' => $order->id, 'nomenclature_id' => $position->nomenclature_id, 'price_without_vat' => $position->price_without_vat])->sum('count');
                    $max_available_count = abs($max_available_count_in_order_position - $common_count_by_consignments);
                    $position->max_available_count = $max_available_count;
                    return $position;
                });
                return $order;
            });


//            $orders->map(function ($order) {
//                $nomenclatures = $order->positions->map(function ($position) {
//                    return $position->nomenclature;
//                });
//                unset($order->positions);
//                return $order->nomenclatures = $nomenclatures->unique();
//            });

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
