<?php


namespace App\Http\Controllers\PaymentRegisters;


use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRegisters\CreatePaymentRegisterFormRequest;
use App\Models\Orders\LKK\Order;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\Provider;
use App\Models\References\ProviderContractDocument;
use App\Services\PaymentRegisters\CreatePaymentRegisterService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Safe\Exceptions\PgsqlException;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;

class PaymentRegisterController extends Controller
{
    public function create(CreatePaymentRegisterFormRequest $request)
    {
//        try {
            $payment_register = (new CreatePaymentRegisterService($request->all()))->run();
            return response()->json(['data' => $payment_register]);
//        } catch (ModelNotFoundException $e) {
//            return response()->json(['message' => $e->getMessage()], 404);
//        } catch
//        (\Exception $e) {
//            if ($e->getCode() >= 400 && $e->getCode() < 500)
//                return response()->json(['message' => $e->getMessage()], $e->getCode());
//            else {
//                Log::error($e->getMessage(), $e->getTrace());
//                return response()->json(['message' => 'System error'], 500);
//            }
//        }
    }

    public function searchProviderContracts(Request $request)
    {
        Validator::make($request->all(), [
            'provider_contr_agent_id' => 'required|exists:contr_agents,id',
            'contractor_contr_agent_id' => 'required|exists:contr_agents,id',
        ])->validate();


        try {
            $provider_contracts = DB::table('orders')->select([
                'provider_contracts.id',
                'provider_contracts.number as number',
                'provider_contracts.date as date',
            ])
                ->join('order_providers', 'orders.provider_id', '=', 'order_providers.id')
                ->join('order_contractors', 'orders.contractor_id', '=', 'order_contractors.id')
                ->join('provider_contracts', 'order_providers.provider_contract_id', '=', 'provider_contracts.id')
                ->where('order_providers.contr_agent_id', $request->provider_contr_agent_id)
                ->where('order_contractors.contr_agent_id', $request->contractor_contr_agent_id)
                ->paginate();
            return response()->json($provider_contracts);
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
            'provider_contr_agent_id' => 'required|exists:contr_agents,id',
            'contractor_contr_agent_id' => 'required|exists:contr_agents,id',
            'provider_contract_id' => 'required|exists:provider_contracts,id',
        ])->validate();

        try {
            $orders = Order::query()
                ->whereRelation('provider', 'contr_agent_id', $request->provider_contr_agent_id)
                ->whereRelation('provider', 'provider_contract_id', $request->provider_contract_id)
                ->whereRelation('contractor', 'contr_agent_id', $request->contractor_contr_agent_id)
                ->paginate();

            return response()->json($orders);
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
