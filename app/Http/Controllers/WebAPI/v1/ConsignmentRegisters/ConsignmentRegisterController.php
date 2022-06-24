<?php


namespace App\Http\Controllers\WebAPI\v1\ConsignmentRegisters;


use App\Events\NewStack;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConsignmentRegisters\CreateConsignmentRegisterFormRequest;
use App\Http\Requests\ConsignmentRegisters\UpdateConsignmentRegisterFormRequest;
use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\ConsignmentRegisters\ConsignmentRegisterPosition;
use App\Models\Consignments\Consignment;
use App\Models\Orders\Order;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\ConsignmentRegisters\CreateConsignmentRegisterService;
use App\Services\ConsignmentRegisters\GetConsignmentRegisterService;
use App\Services\ConsignmentRegisters\GetConsignmentRegistersService;
use App\Services\ConsignmentRegisters\UpdateConsignmentRegisterService;
use App\Services\Filters\ConsignmentRegisterFilter;
use App\Services\Filters\ConsignmentRegisterPositionFilter;
use App\Services\Sortings\ConsignmentRegisterPositionSorting;
use App\Services\Sortings\ConsignmentRegisterSorting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ConsignmentRegisterController extends Controller
{

    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function searchOrders(Request $request)
    {
        Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,id',
            'provider_contr_agent_id' => 'required|integer|exists:contr_agents,id',
            'contractor_contr_agent_id' => 'required|integer|exists:contr_agents,id',
            'customer_object_id' => 'required|integer|exists:customer_objects,id',
            'customer_sub_object_id' => 'nullable|integer|exists:customer_sub_objects,id',
        ])->validate();

        $orders = Order::query()
            ->whereRelation('customer', 'organization_id', $request->organization_id)
            ->whereRelation('customer', 'object_id', $request->customer_object_id)
            ->whereRelation('provider', 'contr_agent_id', $request->provider_contr_agent_id)
            ->whereRelation('contractor', 'contr_agent_id', $request->contractor_contr_agent_id)
            ->with('customer.contract');

        if ($request->customer_sub_object_id ?? null)
            $orders = $orders->whereRelation('customer', 'sub_object_id', $request->customer_sub_object_id);

        $orders = $orders->get();

        return response()->json(['data' => $orders]);
    }

    public function searchConsignments(Request $request)
    {
        $data = $request->all();
        Validator::make($data, [
//            'order_ids' => ['required', 'array'],
//            'order_ids.*' => ['required', 'exists:orders,id']
            'organization_id' => 'required|integer|exists:organizations,id',
            'provider_contr_agent_id' => 'required|integer|exists:contr_agents,id',
            'contractor_contr_agent_id' => 'required|integer|exists:contr_agents,id',
            'customer_object_id' => 'required|integer|exists:customer_objects,id',
            'customer_sub_object_id' => 'nullable|integer|exists:customer_sub_objects,id',
            'work_agreement_id' => 'required|integer|exists:work_agreements,id',
        ])->validate();

        $consignments = Consignment::query()
            ->where([
                'organization_id' => $data['organization_id'],
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
                'contractor_contr_agent_id' => $data['contractor_contr_agent_id'],
                'customer_object_id' => $data['customer_object_id'],
                'work_agreement_id' => $data['work_agreement_id'],
                'is_approved' => true,
            ])
            ->with('positions.nomenclature');

        if ($data['customer_sub_object_id'] ?? null)
            $consignments = $consignments->where('customer_sub_object_id', $data['customer_sub_object_id']);
        $consignments = $consignments->get();

        $consignments = $consignments->map(function ($consignment) {
            $consignment->positions->map(function ($position) use ($consignment) {
                $max_available_count_in_consignment_position = (float)$position->count;
                $common_count_by_consignment_registers = (float)ConsignmentRegisterPosition::query()
                    ->where(['consignment_id' => $consignment->id, 'nomenclature_id' => $position->nomenclature_id, 'price_without_vat' => $position->price_without_vat])->sum('count');
                $max_available_count = abs($max_available_count_in_consignment_position - $common_count_by_consignment_registers);
                $position->max_available_count = $max_available_count;
                return $position;
            });
            return $consignment;
        });
        return response()->json(['data' => $consignments]);
    }

    public function index(Request $request, ConsignmentRegisterFilter $filter, ConsignmentRegisterSorting $sorting)
    {
        try {
            return response()->json((new GetConsignmentRegistersService($request->all(), $filter, $sorting))->run());
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

    public function getConsignmentRegister(Request $request, $consignment_register_id)
    {
        try {
            $consignment_register = ConsignmentRegister::query()->with([
                'positions.consignment'
            ]);
            if ($this->user->isProvider()) {
                $consignment_register->where('provider_contr_agent_id', $this->user->contr_agent_id());
            } elseif ($this->user->isContractor()) {
                $consignment_register->where('contractor_contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var ConsignmentRegister $consignment_register */
            $consignment_register = $consignment_register->findOrFail($consignment_register_id);
            $consignment_register = (new GetConsignmentRegisterService($request->all(), $consignment_register))->run();
            return response()->json(['data' => $consignment_register]);
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

    public function getPositions(Request $request, $consignment_register_id, ConsignmentRegisterPositionFilter $filter, ConsignmentRegisterPositionSorting $sorting)
    {
        try {
            $consignment_register = ConsignmentRegister::query()->with([
                'positions.consignment'
            ]);
            if ($this->user->isProvider()) {
                $consignment_register->where('provider_contr_agent_id', $this->user->contr_agent_id());
            } elseif ($this->user->isContractor()) {
                $consignment_register->where('contractor_contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var ConsignmentRegister $consignment_register */
            $consignment_register = $consignment_register->findOrFail($consignment_register_id);
            return response()->json($consignment_register->positions()->filter($filter)->sorting($sorting)->paginate($request->per_page));
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

    public function create(CreateConsignmentRegisterFormRequest $request)
    {
        try {
            $consignment_register = (new CreateConsignmentRegisterService($request->all()))->run();
            return response()->json(['data' => $consignment_register]);
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

    public function update(UpdateConsignmentRegisterFormRequest $request, $consignment_register_id)
    {
        try {
            /** @var ConsignmentRegister $consignment_register */
            $consignment_register = ConsignmentRegister::query()->findOrFail($consignment_register_id);
            throw_if($consignment_register->is_approved,
                new BadRequestException('Невозможно редактировать реестр накладных. Реестр накладных отправлен на согласование', 400));

            $consignment_register = (new UpdateConsignmentRegisterService($request->all(), $consignment_register))->run();
            return response()->json(['data' => $consignment_register]);
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

    public function delete(Request $request, $consignment_register_id)
    {
        try {
            $consignment_register = ConsignmentRegister::query()->findOrFail($consignment_register_id);
            DB::transaction(function () use ($consignment_register) {
                $consignment_register->positions()->delete();
                $consignment_register->delete();
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

    /*роут подрядчика*/
    public function approve(Request $request, $consignment_register_id)
    {
        try {
            $user = auth('webapi')->user();
            /** @var ConsignmentRegister $consignment_register */
            $consignment_register = ConsignmentRegister::query();
            if ($user->isContractor()) {
                $consignment_register->where('contractor_contr_agent_id', $user->contr_agent_id());
            }
            $consignment_register = $consignment_register->findOrFail($consignment_register_id);

            throw_if($consignment_register->contr_agent_status === ConsignmentRegister::CONTRACTOR_STATUS_AGREED
                /*$order->provider_status === Order::PROVIDER_STATUS_PARTIALLY_AGREED*/
                , new BadRequestException('Реестр ТН уже согласован подрядчиком', 400));
            throw_if($consignment_register->contr_agent_status === ConsignmentRegister::CONTRACTOR_STATUS_NOT_AGREED
                , new BadRequestException('Реестр ТН уже отказан подрядчиком', 400));

//            $order->positions()->where('status', '!=', OrderPosition::STATUS_REJECTED)->update(['status' => OrderPosition::STATUS_AGREED]);
//
//            if ($order->positions()->where('status', OrderPosition::STATUS_REJECTED)->count())
//                $order->provider_status = Order::PROVIDER_STATUS_PARTIALLY_AGREED;
//            else
//                $order->provider_status = Order::PROVIDER_STATUS_AGREED;
            $consignment_register->contr_agent_status = ConsignmentRegister::CONTRACTOR_STATUS_AGREED;
//            $order_provider = $order->provider()->firstOrFail();
//            $order_provider->agreed_comment = $request->comment;
            $consignment_register->save();
//            $order->save();

            event(new NewStack($consignment_register,
                    (new ProviderSyncStack())->setProvider($consignment_register->provider),
                    (new ContractorSyncStack())->setContractor($consignment_register->contractor))
            );

            if (in_array($consignment_register->contr_agent_status, [ConsignmentRegister::CONTRACTOR_STATUS_SELF_PURCHASE, ConsignmentRegister::CONTRACTOR_STATUS_AGREED])) {
                event(new NewStack($consignment_register,
                        (new MTOSyncStack()))
                );
            }

            return $consignment_register;
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

    /*роут подрядчика*/
    public function reject(Request $request, $consignment_register_id)
    {
        try {
            $user = auth('webapi')->user();
            /** @var ConsignmentRegister $consignment_register */
            $consignment_register = ConsignmentRegister::query();
            if ($user->isContractor()) {
                $consignment_register->where('contractor_contr_agent_id', $user->contr_agent_id());
            }
            $consignment_register = $consignment_register->findOrFail($consignment_register_id);

            throw_if($consignment_register->contr_agent_status === ConsignmentRegister::CONTRACTOR_STATUS_AGREED /*||
                $order->provider_status === Order::PROVIDER_STATUS_PARTIALLY_AGREED*/
                , new BadRequestException('Реестр ТН уже согласован подрядчиком', 400));
            throw_if($consignment_register->contr_agent_status === ConsignmentRegister::CONTRACTOR_STATUS_NOT_AGREED
                , new BadRequestException('Реестр ТН уже отказан подрядчиком', 400));

//            $order->positions()->update(['status' => OrderPosition::STATUS_REJECTED]);
            $consignment_register->contr_agent_status = ConsignmentRegister::CONTRACTOR_STATUS_NOT_AGREED;
//            $order_provider = $order->provider()->firstOrFail();
//            $order_provider->rejected_comment = $request->comment;
//            $order_provider->save();
            $consignment_register->save();

            event(new NewStack($consignment_register,
                    (new ProviderSyncStack())->setProvider($consignment_register->provider),
                    (new ContractorSyncStack())->setContractor($consignment_register->contractor))
            );

            if (in_array($consignment_register->contr_agent_status, [ConsignmentRegister::CONTRACTOR_STATUS_SELF_PURCHASE, ConsignmentRegister::CONTRACTOR_STATUS_AGREED])) {
                event(new NewStack($consignment_register,
                        (new MTOSyncStack()))
                );
            }

            return $consignment_register;
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
