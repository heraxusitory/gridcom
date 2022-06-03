<?php


namespace App\Http\Controllers\API\MTO\v1\RequestAdditions;


use App\Events\NewStack;
use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\RANomenclatureTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RequestAdditionNomenclatureController extends Controller
{
    public function sync(Request $request)
    {
        $data = $request->all();
        Validator::make($data, [
            'ra_nomenclatures' => 'required|array',
            'ra_nomenclatures.*.id' => 'required|uuid',
            'ra_nomenclatures.*.organization_status' => ['required', Rule::in(RequestAdditionNomenclature::getOrganizationStatuses())],
        ])->validate();

        $data = $data['ra_nomenclatures'];
        try {
            foreach ($data as $item) {
                /** @var RequestAdditionNomenclature $ra_nomenclature */
                $ra_nomenclature = RequestAdditionNomenclature::query()
                    ->where('uuid', $item['id'])->first();
                if ($ra_nomenclature) {
                    $ra_nomenclature->update(['organization_status' => $item['organization_status']]);

                    if (IntegrationUser::where('contr_agent_id', $ra_nomenclature->contr_agent?->id)->first()?->isProvider()) {
                        event(new NewStack($ra_nomenclature,
                            (new ProviderSyncStack())->setProvider($ra_nomenclature->contr_agent),
                        ));
                    }
                    if (IntegrationUser::where('contr_agent_id', $ra_nomenclature->contr_agent?->id)->first()?->isContractor()) {
                        event(new NewStack($ra_nomenclature,
                            (new ContractorSyncStack())->setContractor($ra_nomenclature->contr_agent),
                        ));
                    }
                }
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
                $orders = MTOSyncStack::getModelEntities(RequestAdditionNomenclature::class);
                return fractal()->collection($orders)->transformWith(RANomenclatureTransformer::class)->serializeWith(CustomerSerializer::class);
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
                return response()->json('Из стека удалено ' . $count . ' НСИ(номенклатуры)');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function downloadFile(Request $request, $ra_nomenclature_id)
    {
        try {
            $ra_nomenclature = RequestAdditionNomenclature::query()->where('uuid', $ra_nomenclature_id)->firstOrFail();
            if (Storage::exists($ra_nomenclature->file_url)) {
                return response()->download(Storage::path($ra_nomenclature->file_url));
            }
            return response('', 204);
        } catch
        (ModelNotFoundException $e) {
            return response()->json(['message' => 'Не найдено обьекта НСИ(номенклатура).'], 404);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }
}
