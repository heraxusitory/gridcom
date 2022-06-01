<?php


namespace App\Http\Controllers\API\MTO\v1\RequestAdditions;


use App\Events\NewStack;
use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\RequestAdditions\RequestAdditionObject;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\RAObjectTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RequestAdditionObjectController extends Controller
{
    public function sync(Request $request)
    {
        $data = $request->all();
        Validator::make($data, [
            'ra_objects' => 'required|array',
            'ra_objects.*.id' => 'required|uuid',
            'ra_objects.*.organization_status' => ['required', Rule::in(RequestAdditionObject::getOrganizationStatuses())],
        ])->validate();

        try {
            foreach ($data as $item) {
                /** @var RequestAdditionObject $ra_object */
                $ra_object = RequestAdditionObject::query()
                    ->where('uuid', $item['id'])->first();
                if ($ra_object) {
                    $ra_object->save(['organization_status' => $item['organization_status']]);

                    if (IntegrationUser::where('contr_agent_id', $ra_object->contr_agent?->uuid)->first()?->isProvider()) {
                        event(new NewStack($ra_object,
                            (new ProviderSyncStack())->setProvider($ra_object->contr_agent),
                        ));
                    }
                    if (IntegrationUser::where('contr_agent_id', $ra_object->contr_agent?->uuid)->first()?->isContractor()) {
                        event(new NewStack($ra_object,
                            (new ContractorSyncStack())->setContractor($ra_object->contr_agent),
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
                $orders = MTOSyncStack::getModelEntities(RequestAdditionObject::class);
                return fractal()->collection($orders)->transformWith(RAObjectTransformer::class)->serializeWith(CustomerSerializer::class);
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
                return response()->json('Из стека удалено ' . $count . ' НСИ(обьекты)');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
