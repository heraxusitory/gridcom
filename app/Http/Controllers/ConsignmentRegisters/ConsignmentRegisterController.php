<?php


namespace App\Http\Controllers\ConsignmentRegisters;


use App\Http\Controllers\Controller;
use App\Http\Requests\ConsignmentRegisters\CreateConsignmentRegisterFormRequest;
use App\Http\Requests\ConsignmentRegisters\UpdateConsignmentRegisterFormRequest;
use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\Consignments\Consignment;
use App\Models\Orders\LKK\Order;
use App\Services\ConsignmentRegisters\CreateConsignmentRegisterService;
use App\Services\ConsignmentRegisters\GetConsignmentRegisterService;
use App\Services\ConsignmentRegisters\GetConsignmentRegistersService;
use App\Services\ConsignmentRegisters\UpdateConsignmentRegisterService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ConsignmentRegisterController extends Controller
{
    public function searchOrders(Request $request)
    {
        Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,id',
            'provider_contr_agent_id' => 'required|integer|exists:contr_agents,id',
            'contractor_contr_agent_id' => 'required|integer|exists:contr_agents,id',
            'customer_object_id' => 'required|integer|exists:customer_objects,id',
            'customer_sub_object_id' => 'required|integer|exists:customer_sub_objects,id',
        ])->validate();

        $orders = Order::query()
            ->whereRelation('customer', 'organization_id', $request->organization_id)
            ->whereRelation('customer', 'object_id', $request->customer_object_id)
            ->whereRelation('customer', 'sub_object_id', $request->customer_sub_object_id)
            ->whereRelation('provider', 'contr_agent_id', $request->provider_contr_agent_id)
            ->whereRelation('contractor', 'contr_agent_id', $request->contractor_contr_agent_id)
            ->with('customer.contract')
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function searchConsignments(Request $request)
    {
        Validator::make($request->all(), [
            'order_ids' => ['required', 'array'],
            'order_ids.*' => ['required', 'exists:orders,id']
        ])->validate();

        $consignments = Consignment::query()
            ->whereIn('order_id', $request->order_ids)
            ->with('positions.nomenclature')
            ->get();

        $consignments->map(function ($consignment) {
            $nomenclatures = $consignment->positions->map(function ($position) {
                return $position->nomenclature;
            });
            unset($consignment->positions);
            return $consignment->nomenclatures = $nomenclatures->unique();
        });
        return response()->json(['data' => $consignments]);
    }

    public function index(Request $request)
    {
        try {
            return response()->json((new GetConsignmentRegistersService())->run());
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
            /** @var ConsignmentRegister $consignment_register */
            $consignment_register = ConsignmentRegister::query()->findOrFail($consignment_register_id);
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
}