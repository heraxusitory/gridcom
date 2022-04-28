<?php


namespace App\Http\Controllers\API\MTO\v1\RequestAdditions;


use App\Http\Controllers\Controller;
use App\Models\RequestAdditions\RequestAdditionObject;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\RAObjectTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestAdditionObjectController extends Controller
{
    public function synchronize(Request $request)
    {
        try {
            return DB::transaction(function () {
                $ra_nomenclatures = RequestAdditionObject::query()
                    ->with([
                        'contr_agent', 'work_agreement',
                        'provider_contract', 'organization',
                        'object'
                    ])
                    /*->where('sync_required', true)*/ #todo: расскомментировать в будущем
                    ->get();
//                OrganizationNotification::query()->whereIn('id', $orders->pluck('id'))->update(['sync_required' => false]);#todo: расскомментировать в будущем
                return fractal()->collection($ra_nomenclatures)->transformWith(RAObjectTransformer::class)->serializeWith(CustomerSerializer::class);
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
                $count = RequestAdditionObject::query()
                    ->whereIn('uuid', $request->ids)
                    ->update(['sync_required' => true]);
                return response()->json('В очередь поставлено ' . $count . ' НСИ (объекты)');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
