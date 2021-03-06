<?php


namespace App\Http\Controllers\WebAPI\v1\PaymentRegisters;


use App\Events\NewStack;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRegisters\CreatePaymentRegisterFormRequest;
use App\Http\Requests\PaymentRegisters\UpdatePaymentRegisterFormRequest;
use App\Models\Orders\Order;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\Filters\PaymentRegisterFilter;
use App\Services\Filters\PaymentRegisterPositionFilter;
use App\Services\PaymentRegisters\CreatePaymentRegisterService;
use App\Services\PaymentRegisters\GetPaymentRegisterService;
use App\Services\PaymentRegisters\GetPaymentRegistersService;
use App\Services\PaymentRegisters\UpdatePaymentRegisterService;
use App\Services\Sortings\PaymentRegisterPositionSorting;
use App\Services\Sortings\PaymentRegisterSorting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class PaymentRegisterController extends Controller
{
    public function index(Request $request, PaymentRegisterFilter $filter, PaymentRegisterSorting $sorting)
    {
        try {
            $register_payments = (new GetPaymentRegistersService($request->all(), $filter, $sorting))->run();
            return response()->json($register_payments);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch
        (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function getPaymentRegister(Request $request, $payment_register_id)
    {
        try {
            $register_payment = (new GetPaymentRegisterService($payment_register_id))->run();
            return response()->json(['data' => $register_payment]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch
        (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function getPositions(Request $request, $payment_register_id, PaymentRegisterPositionFilter $filter, PaymentRegisterPositionSorting $sorting)
    {
        try {
            $user = auth('webapi')->user();
            $payment_register = PaymentRegister::query()->with([
                'positions.order'
            ]);
            if ($user->isProvider()) {
                $payment_register->where('provider_contr_agent_id', $user->contr_agent_id());
            } elseif ($user->isContractor()) {
                $payment_register->where('contractor_contr_agent_id', $user->contr_agent_id());
            }
            $payment_register = $payment_register->findOrFail($payment_register_id);
            return response()->json(['data' => $payment_register->positions()->filter($filter)->sorting($sorting)->paginate($request->per_page)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch
        (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }


    public function create(CreatePaymentRegisterFormRequest $request)
    {
        try {
            $payment_register = (new CreatePaymentRegisterService($request->all()))->run();
            return response()->json(['data' => $payment_register], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch
        (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function update(UpdatePaymentRegisterFormRequest $request, $payment_register_id)
    {
        try {
            /* @var PaymentRegister $payment_register */
            $payment_register = PaymentRegister::query()->findOrFail($payment_register_id);
            throw_if(/*$payment_register->customer_status !== PaymentRegister::CUSTOMER_STATUS_DRAFT &&*/
                $payment_register->provider_status !== PaymentRegister::PROVIDER_STATUS_DRAFT,
                new BadRequestException('???????????????????? ?????????????????????????? ???????????? ????????????????. ???????????? ???????????????? ?????????????????? ???? ????????????????????????', 400));

            $payment_register = (new UpdatePaymentRegisterService($request->all(), $payment_register))->run();
            return response()->json(['data' => $payment_register]);
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

    /*???????? ????????????????????*/
    public function approve(Request $request, $payment_register_id)
    {
        try {
            $user = auth('webapi')->user();
            $payment_register = PaymentRegister::query();
            if ($user->isProvider()) {
                $payment_register->whereRelation('provider', 'id', $user->contr_agent_id());
            }
            /** @var PaymentRegister $payment_register */
            $payment_register = $payment_register->findOrFail($payment_register_id);

            throw_if($payment_register->provider_status === PaymentRegister::PROVIDER_STATUS_AGREED
                /*$order->provider_status === Order::PROVIDER_STATUS_PARTIALLY_AGREED*/
                , new BadRequestException('???????????? ???????????????? ?????? ???????????????????? ??????????????????????', 400));
            throw_if($payment_register->provider_status === PaymentRegister::PROVIDER_STATUS_NOT_AGREED
                , new BadRequestException('???????????? ???????????????? ?????? ?????????????? ??????????????????????', 400));

//            $order->positions()->where('status', '!=', OrderPosition::STATUS_REJECTED)->update(['status' => OrderPosition::STATUS_AGREED]);
//
//            if ($order->positions()->where('status', OrderPosition::STATUS_REJECTED)->count())
//                $order->provider_status = Order::PROVIDER_STATUS_PARTIALLY_AGREED;
//            else
//                $order->provider_status = Order::PROVIDER_STATUS_AGREED;
            $payment_register->provider_status = PaymentRegister::PROVIDER_STATUS_AGREED;
//            $order_provider = $order->provider()->firstOrFail();
//            $order_provider->agreed_comment = $request->comment;
            $payment_register->save();
//            $order->save();

            event(new NewStack($payment_register,
                    (new ProviderSyncStack())->setProvider($payment_register->provider),
                    (new ContractorSyncStack())->setContractor($payment_register->contractor),
                    (new MTOSyncStack()))
            );

            return $payment_register;
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


    /*provider's route*/
    public function reject(Request $request, $payment_register_id)
    {
        try {
            $user = auth('webapi')->user();
            $payment_register = PaymentRegister::query();
            if ($user->isProvider()) {
                $payment_register->whereRelation('provider', 'id', $user->contr_agent_id());
            }
            /** @var PaymentRegister $payment_register */
            $payment_register = $payment_register->findOrFail($payment_register_id);

            throw_if($payment_register->provider_status === PaymentRegister::PROVIDER_STATUS_AGREED /*||
                $order->provider_status === Order::PROVIDER_STATUS_PARTIALLY_AGREED*/
                , new BadRequestException('???????????? ???????????????? ?????? ???????????????????? ??????????????????????', 400));
            throw_if($payment_register->provider_status === PaymentRegister::PROVIDER_STATUS_NOT_AGREED
                , new BadRequestException('???????????? ???????????????? ?????? ?????????????? ??????????????????????', 400));

//            $order->positions()->update(['status' => OrderPosition::STATUS_REJECTED]);
            $payment_register->provider_status = PaymentRegister::PROVIDER_STATUS_NOT_AGREED;
//            $order_provider = $order->provider()->firstOrFail();
//            $order_provider->rejected_comment = $request->comment;
//            $order_provider->save();
            $payment_register->save();

            event(new NewStack($payment_register,
                    (new ProviderSyncStack())->setProvider($payment_register->provider),
                    (new ContractorSyncStack())->setContractor($payment_register->contractor),
                /*(new MTOSyncStack())*/)
            );

            return $payment_register;
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

    public function delete(Request $request, $register_payment_id)
    {
        try {
            $register_payment = PaymentRegister::query()->findOrFail($register_payment_id);
            DB::transaction(function () use ($register_payment) {
                $register_payment->positions()->delete();
                $register_payment->delete();
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

    public function searchProviderContracts(Request $request)
    {
        Validator::make($request->all(), [
            'provider_contr_agent_id' => 'required|exists:contr_agents,id',
            'contractor_contr_agent_id' => ['required', 'exists:contr_agents,id', Rule::in([Auth::user()->contr_agent_id()])],
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
            'contractor_contr_agent_id' => ['required', 'exists:contr_agents,id', Rule::in([Auth::user()->contr_agent_id()])],
            'provider_contract_id' => 'required|exists:provider_contracts,id',
        ])->validate();

        try {
            $orders = Order::query()
                ->whereRelation('provider', 'contr_agent_id', $request->provider_contr_agent_id)
                ->whereRelation('provider', 'provider_contract_id', $request->provider_contract_id)
                ->whereRelation('contractor', 'contr_agent_id', $request->contractor_contr_agent_id)
                ->get();

            return response()->json(['data' => $orders]);
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
