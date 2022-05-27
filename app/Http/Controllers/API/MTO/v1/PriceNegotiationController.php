<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Http\Controllers\Controller;
use App\Models\Notifications\OrganizationNotification;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\SyncStacks\MTOSyncStack;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\PriceNegotiationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PriceNegotiationController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'price_negotiations' => 'required|array',
            'price_negotiations.*.id' => 'required|uuid',
            'price_negotiations.*.organization_status' => ['required', Rule::in(PriceNegotiation::getOrganizationStatuses())]
        ]);
        try {
            foreach ($request['price_negotiations'] as $price_negotiation) {
                PriceNegotiation::query()->where('uuid', $price_negotiation['id'])->update(['organization_status' => $price_negotiation['organization_status']]);
            }
            return response()->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function synchronize(Request $request)
    {
        try {
            return DB::transaction(function () {
                $orders = MTOSyncStack::getModelEntities(PriceNegotiation::class);
                return fractal()->collection($orders)->transformWith(PriceNegotiationTransformer::class)->serializeWith(CustomerSerializer::class);
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
                return response()->json('Из стека удалено ' . $count . ' согласований цен.');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
