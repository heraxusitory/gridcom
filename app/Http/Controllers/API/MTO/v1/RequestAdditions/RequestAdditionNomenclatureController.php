<?php


namespace App\Http\Controllers\API\MTO\v1\RequestAdditions;


use App\Http\Controllers\Controller;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\RANomenclatureTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestAdditionNomenclatureController extends Controller
{
    public function synchronize(Request $request)
    {
        try {
            return DB::transaction(function () {
                $ra_nomenclatures = RequestAdditionNomenclature::query()
                    ->with([
                    ])
                    /*->where('sync_required', true)*/ #todo: расскомментировать в будущем
                    ->get();
//                OrganizationNotification::query()->whereIn('id', $orders->pluck('id'))->update(['sync_required' => false]);#todo: расскомментировать в будущем
                return fractal()->collection($ra_nomenclatures)->transformWith(RANomenclatureTransformer::class)->serializeWith(CustomerSerializer::class);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
