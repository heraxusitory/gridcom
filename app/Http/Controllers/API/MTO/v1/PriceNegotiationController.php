<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Http\Controllers\Controller;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\PriceNegotiationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceNegotiationController extends Controller
{
    public function synchronize()
    {
        try {
            return DB::transaction(function () {
                $price_negotiations = PriceNegotiation::query()
                    ->with([

                    ])
                    /*->where('sync_required', true)*/ #todo: расскомментировать в будущем
                    ->get();
//                PriceNegotiation::query()->whereIn('id', $orders->pluck('id'))->update(['sync_required' => false]);#todo: расскомментировать в будущем
                return fractal()->collection($price_negotiations)->transformWith(PriceNegotiationTransformer::class)->serializeWith(CustomerSerializer::class);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function putInQueue(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|uuid',
        ]);
        try {
            return DB::transaction(function () use ($request) {
                $count = PriceNegotiation::withoutEvents(function () use ($request) {
                    return PriceNegotiation::query()
                        ->whereIn('uuid', $request->ids)
                        ->update(['sync_required' => true]);
                });
                return response()->json('В очередь поставлено ' . $count . ' сущностей по согласованию цен');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
