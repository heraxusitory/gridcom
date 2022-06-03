<?php


namespace App\Http\Controllers\API\ContrAgents;


use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\RequestAdditions\RequestAdditionObject;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Services\API\ContrAgents\v1\CreateOrUpdateRAObjectService;
use App\Transformers\API\ContrAgents\v1\RAObjectTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RequestAdditionObjectController extends Controller
{
    public function sync(Request $request)
    {
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();

        $data = $request->all();
        Validator::make($data, [
            'ra_objects' => 'required|array',
            'ra_objects.*.id' => 'required|uuid',
            'ra_objects.*.type' => ['required', Rule::in(['new', 'change'])],
            'ra_objects.*.number' => 'required|string|max:255',
            'ra_objects.*.date' => 'required|date_format:Y-m-d',
            'ra_objects.*.work_agreement.number' => ['nullable', Rule::requiredIf(function () use ($user) {
                return $user->isContractor();
            }), 'string', 'max:255'],
            'ra_objects.*.provider_contract.number' => ['nullable', Rule::requiredIf(function () use ($user) {
                return $user->isProvider();
            }), 'string', 'max:255'],
            'ra_objects.*.organization.name' => 'required|string|max:255',
            'ra_objects.*.object.name' => 'required|string|max:255',
            'ra_objects.*.description' => 'nullable|string',
            'ra_objects.*.responsible_full_name' => 'nullable|string|max:255',
            'ra_objects.*.contr_agent_comment' => 'nullable|string|max:255',
            'ra_objects.*.file' => 'nullable|file',
        ])->validate();

        try {
            $data = $data['ra_objects'];
            (new CreateOrUpdateRAObjectService($data, $user))->run();
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
                /** @var IntegrationUser $user */
                $user = Auth::guard('api')->user();
                if ($user->isContractor())
                    $pr = ContractorSyncStack::getModelEntities(RequestAdditionObject::class, $user->contr_agent);
                else if ($user->isProvider())
                    $pr = ProviderSyncStack::getModelEntities(RequestAdditionObject::class, $user->contr_agent);
                else $pr = [];
                return fractal()->collection($pr)->transformWith(RAObjectTransformer::class)->serializeWith(CustomerSerializer::class);
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
                return response()->json('Из стека удалено ' . $count . ' НСИ(объекты)');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function downloadFile(Request $request, $ra_object_id)
    {
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();
        try {
            $ra_object = RequestAdditionObject::query()->where(['uuid' => $ra_object_id, 'contr_agent_id' => $user->contr_agent()->id])->firstOrFail();
            if (Storage::exists($ra_object->file_url)) {
                return response()->download(storage_path($ra_object->file_url));
            }
            return response('', 204);
        } catch
        (ModelNotFoundException $e) {
            return response()->json(['message' => 'Не найдено обьекта НСИ(объект).'], 404);
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
