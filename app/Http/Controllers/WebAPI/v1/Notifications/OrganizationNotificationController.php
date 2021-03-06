<?php

namespace App\Http\Controllers\WebAPI\v1\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\OrganizationNotification\CreateOrganizationNotificationFormRequest;
use App\Http\Requests\Notifications\OrganizationNotification\UpdateOrganizationNotificationFormRequest;
use App\Models\Notifications\OrganizationNotification;
use App\Models\ProviderOrders\ProviderOrder;
use App\Services\Filters\OrganizationNotificationFilter;
use App\Services\Filters\OrganizationNotificationPositionFilter;
use App\Services\OrganizationNotifications\CreateOrganizationNotificationService;
use App\Services\OrganizationNotifications\UpdateOrganizationNotificationService;
use App\Services\Sortings\OrganizationNotificationPositionSorting;
use App\Services\Sortings\OrganizationNotificationSorting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OrganizationNotificationController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function index(Request $request, OrganizationNotificationFilter $filter, OrganizationNotificationSorting $sorting)
    {
        try {
            $organization_notifications = OrganizationNotification::query()->filter($filter);
            if ($this->user->isProvider()) {
                $organization_notifications->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $organization_notifications = $organization_notifications->sorting($sorting)->get();
            return response()->json($organization_notifications);
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

    public function getNotification(Request $request, $organization_notification_id)
    {
        try {
            $organization_notification = OrganizationNotification::query();
            if ($this->user->isProvider()) {
                $organization_notification->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $organization_notification = $organization_notification->findOrFail($organization_notification_id);
            return response()->json(['data' => $organization_notification]);
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

    public function getNotificationPositions(Request $request, $organization_notification_id, OrganizationNotificationPositionFilter $filter, OrganizationNotificationPositionSorting $sorting)
    {
        try {
            $organization_notification = OrganizationNotification::query();
            if ($this->user->isProvider()) {
                $organization_notification->where('provider_contr_agent_id', $this->user->contr_agent_id());
            }
            $organization_notification = $organization_notification->findOrFail($organization_notification_id);
            return response()->json($organization_notification->positions()->filter($filter)->sorting($sorting)->paginate($request->per_page));
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

    public function create(CreateOrganizationNotificationFormRequest $request)
    {
        try {
            return response()->json(['data' => (new CreateOrganizationNotificationService($request->all()))->run()]);

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

    public function update(UpdateOrganizationNotificationFormRequest $request, $organization_notification_id)
    {
        try {
            /** @var OrganizationNotification $organization_notification */
            $organization_notification = OrganizationNotification::query()->findOrFail($organization_notification_id);
            throw_if($organization_notification->status !== OrganizationNotification::ORGANIZATION_STATUS_DRAFT
                , new BadRequestException('?????????????????????? ?????? ???????????????????? ???? ???????????????????????? ??????????????', 400));

            return response()->json(['data' => (new UpdateOrganizationNotificationService($request->all(), $organization_notification))->run()]);
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

    public function searchContracts(Request $request)
    {
        $data = $request->all();
        Validator::validate($data, [
            'organization_id' => ['required', 'exists:organizations,id'],
            'provider_contr_agent_id' => ['required', 'exists:contr_agents,id', Rule::in([Auth::user()->contr_agent_id()])],
        ]);

        $contracts = ProviderOrder::query()
            ->select('contract_number', 'contract_date')
            ->where('organization_id', $data['organization_id'])
            ->where('provider_contr_agent_id', $data['provider_contr_agent_id'])
            ->get()
            ->map(function ($order) {
                return [
                    'contract_date' => $order->contract_date,
                    'contract_number' => $order->contract_number,
                ];
            })->unique();

        return response()->json(['data' => $contracts]);
    }

    public function searchOrders(Request $request)
    {
        $data = $request->all();
        Validator::validate($data, [
            'organization_id' => ['required', 'exists:organizations,id'],
            'provider_contr_agent_id' => ['required', 'exists:contr_agents,id', Rule::in([Auth::user()->contr_agent_id()])],
            'contract_number' => 'required|string|max:255',
            'contract_date' => 'required|date_format:Y-m-d',
//            'contract_stage' => ['required'/*, Rule::in(ProviderOrder::STAGES())*/]
        ]);

        $orders = ProviderOrder::query()
            ->where('organization_id', $data['organization_id'])
            ->where('provider_contr_agent_id', $data['provider_contr_agent_id'])
            ->where('contract_number', $data['contract_number'])
            ->where('contract_date', $data['contract_date'])
//            ->where('contract_stage', $data['contract_stage'])
            ->with(['actual_positions.nomenclature'])
            ->get();

        return response()->json(['data' => $orders]);
    }
}
