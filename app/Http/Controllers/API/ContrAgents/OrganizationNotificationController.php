<?php


namespace App\Http\Controllers\API\ContrAgents;


use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\Notifications\OrganizationNotification;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\ContrAgents\v1\OrganizationNotificationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizationNotificationController extends Controller
{
//    public function sync(Request $request)
//    {
//        try {
//            $request->validate([
//                'id' => 'required|uuid',
//                'status' => ['required', Rule::in(OrganizationNotification::getOrganizationStatuses())]
//            ]);
//            OrganizationNotification::query()->where('uuid', $request->id)->update(['status', $request->status]);
//            return response()->json();
//        } catch (\Exception $e) {
//            Log::error($e->getMessage(), $e->getTrace());
//            return response()->json(['message' => 'System error'], 500);
//        }
//    }

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
