<?php


namespace App\Http\Controllers\API\MTO\v1\Notifications;


use App\Events\NewStack;
use App\Http\Controllers\Controller;
use App\Models\Notifications\OrganizationNotification;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\OrganizationNotificationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrganizationNotificationController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'organization_notifications' => 'required|array',

            'organization_notifications.*.id' => 'required|uuid',
            'organization_notifications.*.status' => ['required', Rule::in(OrganizationNotification::getOrganizationStatuses())],
            'organization_notifications.*.organization_comment' => ['nullable', 'string']
        ]);
        try {
            foreach ($request['organization_notifications'] as $notification) {
                /** @var OrganizationNotification $organization_notification */
                $organization_notification = OrganizationNotification::query()->where('uuid', $notification['id'])->first();
                if ($organization_notification) {
                    $organization_notification->update(['status' => $notification['status'], 'organization_comment' => $notification['organization_comment'] ?? null]);
                    event(new NewStack($organization_notification,
                        (new ProviderSyncStack())->setProvider($organization_notification->provider),
                    ));
                }
            }
            return response()->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function synchronize()
    {
        try {
            return DB::transaction(function () {
                $orders = MTOSyncStack::getModelEntities(OrganizationNotification::class);
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
                $count = MTOSyncStack::destroy($request->stack_ids);
                return response()->json('Из стека удалено ' . $count . ' уведомлений о поставке филиалов.');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
