<?php


namespace App\Http\Controllers\API\MTO\v1\Notifications;


use App\Http\Controllers\Controller;
use App\Models\Notifications\OrganizationNotification;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\OrganizationNotificationTransformer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizationNotificationController extends Controller
{
    public function synchronize()
    {
        try {
            return DB::transaction(function () {
                $notifications = OrganizationNotification::query()
                    ->with([
                        'organization', 'provider',
                        'positions.order', 'positions.nomenclature',
                    ])
                    /*->where('sync_required', true)*/ #todo: расскомментировать в будущем
                    ->get();
//                OrganizationNotification::query()->whereIn('id', $orders->pluck('id'))->update(['sync_required' => false]);#todo: расскомментировать в будущем
                return fractal()->collection($notifications)->transformWith(OrganizationNotificationTransformer::class)->serializeWith(CustomerSerializer::class);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
