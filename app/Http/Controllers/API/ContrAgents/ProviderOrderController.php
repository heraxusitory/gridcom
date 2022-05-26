<?php


namespace App\Http\Controllers\API\ContrAgents;


use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\ContrAgents\v1\ProviderOrderTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProviderOrderController extends Controller
{
    public function synchronize(Request $request)
    {
        try {
            return DB::transaction(function () {
                /** @var IntegrationUser $user */
                $user = Auth::guard('api')->user();
                if ($user->isProvider())
                    $pr = ProviderSyncStack::getModelEntities(ProviderOrder::class, $user->contr_agent);
                else $pr = [];
                return fractal()->collection($pr)->transformWith(ProviderOrderTransformer::class)->serializeWith(CustomerSerializer::class);
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
                /** @var IntegrationUser $user */
                $user = Auth::guard('api')->user();
                $count = 0;
                if ($user->isProvider())
                    $count = ProviderSyncStack::destroy($request->stack_ids);
                return response()->json('Из стека удалено ' . $count . ' заказов поставщику филилалов.');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
