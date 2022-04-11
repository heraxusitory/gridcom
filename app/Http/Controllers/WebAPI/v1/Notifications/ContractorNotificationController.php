<?php

namespace App\Http\Controllers\WebAPI\v1\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\ContractorNotification\CreateContractorNotificationFormRequest;
use App\Http\Requests\Notifications\ContractorNotification\UpdateContractorNotificationFormRequest;
use App\Models\Notifications\ContractorNotification;
use App\Models\Orders\LKK\Order;
use App\Services\ContractorNotifications\CreateContractorNotificationService;
use App\Services\ContractorNotifications\GetContractorNotificationService;
use App\Services\ContractorNotifications\IndexContractorNotificationService;
use App\Services\ContractorNotifications\UpdateContractorNotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ContractorNotificationController extends Controller
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function searchProviderContracts(Request $request)
    {
        $data = $request->all();
        Validator::validate($data, [
            'contractor_contr_agent_id' => ['required', 'exists:contr_agents,id'],
            'provider_contr_agent_id' => ['required', 'exists:contr_agents,id'],
        ]);

        $orders = Order::query()
            ->whereRelation('contractor', 'contr_agent_id', $data['contractor_contr_agent_id'])
            ->whereRelation('provider', 'contr_agent_id', $data['provider_contr_agent_id'])
            ->with(['provider.provider_contract', 'positions'])->get();

        $provider_contracts = $orders->map(function ($order) {
            return $order->provider->provider_contract;
        })->unique();
        return response()->json(['data' => $provider_contracts]);
    }

    public function searchOrders(Request $request)
    {
        $data = $request->all();
        Validator::validate($data, [
            'contractor_contr_agent_id' => ['required', 'exists:contr_agents,id'],
            'provider_contr_agent_id' => ['required', 'exists:contr_agents,id'],
        ]);

        $order_query = Order::query()
            ->whereRelation('contractor', 'contr_agent_id', $data['contractor_contr_agent_id'])
            ->whereRelation('provider', 'contr_agent_id', $data['provider_contr_agent_id'])
            ->with(['provider.provider_contract']);

        $provider_contract_ids = $order_query->get()->map(function ($order) {
            return $order->provider->provider_contract->id;
        })->unique();

        Validator::validate($data, [
            'provider_contract_id' => ['required', Rule::in($provider_contract_ids)],
        ]);


        $orders = $order_query
            ->with(['customer', 'provider', 'contractor', 'positions.nomenclature'])
            ->get();

        $orders->map(function ($order) {
            $nomenclatures = $order->positions->map(function ($position) {
                return $position->nomenclature;
            });
            unset($order->positions);
            return $order->nomenclatures = $nomenclatures->unique();
        });

        return response()->json(['data' => $orders]);
    }

    public function index(Request $request)
    {
        try {
            return response()->json((new IndexContractorNotificationService())->run());
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

    public function getNotification(Request $request, $contractor_notification_id)
    {
        try {
            $contractor_notification = ContractorNotification::query();
            if ($this->user->isProvider()) {
                $contractor_notification->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            if ($this->user->isContractor()) {
                $contractor_notification->where('contractor_contr_agent_id', $this->user->contr_agent_id());
            }
            /** @var ContractorNotification $contractor_notification */
            $contractor_notification->findOrFail($contractor_notification_id);
            return response()->json(['data' => (new GetContractorNotificationService($request->all(), $contractor_notification))->run()]);
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

    public function create(CreateContractorNotificationFormRequest $request)
    {
        try {
            return response()->json(['data' => (new CreateContractorNotificationService($request->all()))->run()]);
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

    public function update(UpdateContractorNotificationFormRequest $request, $contractor_notification_id)
    {
        try {
            /** @var ContractorNotification $contractor_notification */
            $contractor_notification = ContractorNotification::query()->findOrFail($contractor_notification_id);
            throw_if($contractor_notification->status !== ContractorNotification::CONTRACTOR_STATUS_DRAFT
                , new BadRequestException('Уведомление уже отправлено на согласование подрядчику', 400));
            return response()->json(['data' => (new UpdateContractorNotificationService($request->all(), $contractor_notification))->run()]);
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

    public function delete(Request $request)
    {
        try {

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
