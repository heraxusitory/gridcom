<?php


namespace App\Http\Controllers\API\ContrAgents;


use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\Notifications\OrganizationNotification;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Services\API\ContrAgents\v1\CreateOrUpdateOrganizationNotificationService;
use App\Transformers\API\ContrAgents\v1\OrganizationNotificationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrganizationNotificationController extends Controller
{
    public function sync(Request $request)
    {
        $user = Auth::guard('api')->user();
        $request->validate([
            'organization_notifications' => 'required|array',
            'organization_notifications.*.id' => 'required|uuid',
            'organization_notifications.*.date' => 'required|date_format:Y-m-d',
//                'organization_notifications.*.status' => 'required|date_format:Y-m-d',
//                'organization_notifications.*.contract_stage' => 'nullable|string|max:255',
            'organization_notifications.*.organization.name' => 'required|string|max:255',
            'organization_notifications.*.provider_contr_agent.name' => 'required|string|max:255',
            'organization_notifications.*.contract_number' => 'required|string|max:255',
            'organization_notifications.*.contract_date' => 'required|string|max:255',
            'organization_notifications.*.date_fact_delivery' => 'nullable|string|max:255',
            'organization_notifications.*.delivery_address' => 'nullable|string|max:255',
            'organization_notifications.*.car_info' => 'nullable|string|max:255',
            'organization_notifications.*.driver_phone' => 'nullable|string|max:255',
            'organization_notifications.*.responsible_full_name' => 'nullable|string|max:255',
            'organization_notifications.*.responsible_phone' => 'nullable|string|max:255',

            'organization_notifications.*.positions' => 'required|array',
            'organization_notifications.*.positions.*.position_id' => 'required|uuid',
            'organization_notifications.*.positions.*.order_id' => 'required|uuid|exists:provider_orders,uuid',
            'organization_notifications.*.positions.*.price_without_vat' => 'required|numeric',
            'organization_notifications.*.positions.*.nomenclature.name' => 'required|string|max:255',
            'organization_notifications.*.positions.*.nomenclature.mnemocode' => 'required|string|max:255',
            'organization_notifications.*.positions.*.count' => 'required|numeric',
            'organization_notifications.*.positions.*.vat_rate' => ['required', 'numeric', Rule::in(array_keys(config('vat_rates')))],
        ]);

        try {
            $data = $request->all()['organization_notifications'];
            (new CreateOrUpdateOrganizationNotificationService($data, $user))->run();
            return response()->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function synchronize()
    {
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();
        try {
            return DB::transaction(function () use ($user) {
                if ($user->isProvider())
                    $orders = ProviderSyncStack::getModelEntities(OrganizationNotification::class, $user->contr_agent);
                else $orders = [];
                return fractal()->collection($orders)->transformWith(OrganizationNotificationTransformer::class)->serializeWith(CustomerSerializer::class);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }


    public function removeFromStack(Request $request)
    {
        $request->validate([
            'stack_ids' => 'required|array',
            'stack_ids.*' => 'required|uuid',
        ]);
        try {
            return DB::transaction(function () use ($request) {
                $count = ProviderSyncStack::destroy($request->stack_ids);
                return response()->json('Из стека удалено ' . $count . ' уведомлений о поставке филиалов.');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
