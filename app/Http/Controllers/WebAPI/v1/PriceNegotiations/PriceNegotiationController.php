<?php

namespace App\Http\Controllers\WebAPI\v1\PriceNegotiations;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceNegotiations\CreatePriceNegotiationFormRequest;
use App\Http\Requests\PriceNegotiations\UpdatePriceNegotiationFormRequest;
use App\Models\Orders\Order;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\CustomerObject;
use App\Services\PriceNegotiations\CreatePriceNegotiationService;
use App\Services\PriceNegotiations\UpdatePriceNegotiationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class PriceNegotiationController extends Controller
{
    public function searchOrdersWithNomenclature(Request $request)
    {
        $data = $request->all();
        Validator::validate($data, [
            'type' => ['required', Rule::in(PriceNegotiation::TYPES())],
        ]);

        switch ($data['type']) {
            case  PriceNegotiation::TYPE_CONTRACT_WORK():
                Validator::validate($data, [
                    'object_id' => 'required|exists:customer_objects,id',
                ]);

                $object = CustomerObject::query()->findOrFail($data['object_id']);
                $sub_object_ids = $object->subObjects()->pluck('id');
                Validator::validate($data, [
                    'sub_object_id' => ['required', 'exists:customer_sub_objects,id', Rule::in($sub_object_ids)]
                ]);

                Validator::validate($data, [
                    'provider_contr_agent_id' => 'required|exists:contr_agents,id',
                    'contractor_contr_agent_id' => 'required|exists:contr_agents,id',
                    'organization_id' => 'required|exists:organizations,id',
                ]);

                $orders = Order::query()
                    ->whereRelation('contractor', 'contr_agent_id', $data['contractor_contr_agent_id'])
                    ->whereRelation('customer', 'organization_id', $data['organization_id'])
                    ->whereRelation('customer', 'object_id', $data['object_id'])
                    ->whereRelation('customer', 'sub_object_id', $data['sub_object_id'])
                    ->with(['positions.nomenclature', 'customer', 'contractor', 'provider'])
                    ->get();

                $orders->map(function ($order) {
                    $nomenclatures = $order->positions->map(function ($position) {
                        return $position->nomenclature;
                    });
                    unset($order->positions);
                    return $order->nomenclatures = $nomenclatures->unique();
                });
                return response()->json($orders);

            case PriceNegotiation::TYPE_CONTRACT_HOME_METHOD():
                Validator::validate($data, [
                    'provider_contr_agent_id' => 'required|exists:contr_agents,id',
                    'organization_id' => 'required|exists:organizations,id',
                ]);
                $orders = ProviderOrder::query()
                    ->where('provider_contr_agent_id', $data['provider_contr_agent_id'])
                    ->where('organization_id', $data['organization_id'])
                    ->with('actual_positions.nomenclature')
                    ->get();

                $orders->map(function ($order) {
                    $nomenclatures = $order->actual_positions->map(function ($position) {
                        return $position->nomenclature;
                    });
                    unset($order->actual_positions);
                    return $order->nomenclatures = $nomenclatures->unique();
                });
                return response()->json($orders);
            default:
                throw new BadRequestException('Type is required', 400);
        }
    }

    public function create(CreatePriceNegotiationFormRequest $request)
    {
        try {
            return response()->json(['data' => (new CreatePriceNegotiationService($request->all()))->run()]);
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

    public function update(UpdatePriceNegotiationFormRequest $request, $price_negotiation_id)
    {
        try {
            /** @var PriceNegotiation $price_negotiation */
            $price_negotiation = PriceNegotiation::query()->findOrFail($price_negotiation_id);
            throw_if($price_negotiation->organization_status !== PriceNegotiation::ORGANIZATION_STATUS_DRAFT,
                new BadRequestException('Невоможно редактировать запрос на согласование цен. Запрос на согласование цен уже отправлен на согласование', 400));
            return response()->json(['data' => (new UpdatePriceNegotiationService($request->all(), $price_negotiation))->run()]);
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

    public function index(Request $request)
    {
        $user = auth('webapi')->user();
        try {
            $price_negotiations = PriceNegotiation::query()->with(['positions']);

            if ($user->isProvider()) {
                $price_negotiations->where('type', PriceNegotiation::TYPE_CONTRACT_HOME_METHOD())->get()->map(function ($negotiation) use ($user) {
                    $negotiation->order = $negotiation->order()->where('provider_contr_agent_id', $user->contr_agent_id())->first();
                    return $negotiation;
                })->filter(function ($negotiation) {
                    return $negotiation->order;
                });
            } elseif ($user->isContractor()) {
                $price_negotiations->where('type', PriceNegotiation::TYPE_CONTRACT_WORK())->get()->map(function ($negotiation) use ($user) {
                    $negotiation->order = $negotiation->order()->whereRelation('contractor', 'contr_agent_id', $user->contr_agent_id())->first();
                    return $negotiation;
                })->filter(function ($negotiation) {
                    return $negotiation->order;
                });
            }

//            return response()->json(new Paginator($price_negotiations, 15));
            return response()->json(['data' => $price_negotiations]);
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

    public function getPriceNegotiation(Request $request, $price_negotiation_id)
    {
        $user = auth('webapi')->user();
        try {
            $price_negotiation = PriceNegotiation::query()->with(['positions']);

            if ($user->isProvider()) {
                $price_negotiation = $price_negotiation->where('type', PriceNegotiation::TYPE_CONTRACT_HOME_METHOD())
                    ->findOrFail($price_negotiation_id);
                $price_negotiation->order = $price_negotiation->order()->where('provider_contr_agent_id', $user->contr_agent_id())->first();
            } elseif ($user->isContractor()) {

                $price_negotiation = $price_negotiation->where('type', PriceNegotiation::TYPE_CONTRACT_WORK())
                    ->findOrFail($price_negotiation_id);
                $price_negotiation->order = $price_negotiation->order()->whereRelation('contractor', 'contr_agent_id', $user->contr_agent_id())->first();
            } else {
//            $price_negotiation = $price_negotiation->;
                $price_negotiation = $price_negotiation->findOrFail($price_negotiation_id);
                $price_negotiation->order = $price_negotiation->order()->first();
            }

            return response()->json(['data' => $price_negotiation]);
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

    public function delete(Request $request, $price_negotiation_id)
    {
        try {
            $price_negotiation = PriceNegotiation::query()->findOrFail($price_negotiation_id);

            DB::transaction(function () use ($price_negotiation) {
                $price_negotiation->positions()->delete();
                $price_negotiation->delete();
            });
            return response()->json('', 204);
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
