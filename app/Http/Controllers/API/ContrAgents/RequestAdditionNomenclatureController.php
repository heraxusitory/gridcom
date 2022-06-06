<?php


namespace App\Http\Controllers\API\ContrAgents;


use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Services\API\ContrAgents\v1\CreateOrUpdateRANomenclatureService;
use App\Transformers\API\ContrAgents\v1\RANomenclatureTransformer;
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
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();

        $data = $request->all();
        Validator::make($data, [
            'ra_nomenclatures' => 'required|array',
            'ra_nomenclatures.*.id' => 'required|uuid',
            'ra_nomenclatures.*.type' => ['required', Rule::in([RequestAdditionNomenclature::TYPE_CHANGE(), RequestAdditionNomenclature::TYPE_NEW()])],
            'ra_nomenclatures.*.number' => 'required|string|max:255',
            'ra_nomenclatures.*.date' => 'required|date_format:Y-m-d',
            'ra_nomenclatures.*.work_agreement.number' => ['nullable', Rule::requiredIf(function () use ($user) {
                return $user->isContractor();
            }), 'string', 'max:255'],
            'ra_nomenclatures.*.provider_contract.number' => ['nullable', Rule::requiredIf(function () use ($user) {
                return $user->isProvider();
            }), 'string', 'max:255'],
            'ra_nomenclatures.*.organization.name' => 'required|string|max:255',
        ])->validate();

        Validator::make($data, [
            'ra_nomenclatures.*.nomenclature.mnemocode' => ['nullable', 'required_if:ra_nomenclatures.*.type,' . RequestAdditionNomenclature::TYPE_CHANGE(), 'string', 'max:255'],
            'ra_nomenclatures.*.nomenclature.name' => ['nullable', 'required_if:ra_nomenclatures.*.type,' . RequestAdditionNomenclature::TYPE_NEW(), 'string', 'max:255'],
            'ra_nomenclatures.*.nomenclature.unit' => ['nullable', 'required_if:ra_nomenclatures.*.type,' . RequestAdditionNomenclature::TYPE_NEW(), 'string', 'max:255'],
            'ra_nomenclatures.*.description' => 'nullable|string',
            'ra_nomenclatures.*.responsible_full_name' => 'nullable|string|max:255',
            'ra_nomenclatures.*.contr_agent_comment' => 'nullable|string|max:255',
            'ra_nomenclatures.*.file' => 'nullable|file',])->validate();

        try {
            $data = $data['ra_nomenclatures'];
            (new CreateOrUpdateRANomenclatureService($data, $user))->run();
            return response()->json();
        } catch
        (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function synchronize(Request $request)
    {
        try {
            return DB::transaction(function () {
                /** @var IntegrationUser $user */
                $user = Auth::guard('api')->user();
                if ($user->isContractor())
                    $pr = ContractorSyncStack::getModelEntities(RequestAdditionNomenclature::class, $user->contr_agent);
                else if ($user->isProvider())
                    $pr = ProviderSyncStack::getModelEntities(RequestAdditionNomenclature::class, $user->contr_agent);
                else $pr = [];
                return fractal()->collection($pr)->transformWith(RANomenclatureTransformer::class)->serializeWith(CustomerSerializer::class);
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
                elseif ($user->isContractor())
                    $count = ContractorSyncStack::destroy($request->stack_ids);
                return response()->json('Из стека удалено ' . $count . ' НСИ(номенклатуры)');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function downloadFile(Request $request, $ra_nomenclature_id)
    {
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();
        try {
            $ra_nomenclature = RequestAdditionNomenclature::query()->where(['uuid' => $ra_nomenclature_id, 'contr_agent_id' => $user->contr_agent?->id])->firstOrFail();
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
