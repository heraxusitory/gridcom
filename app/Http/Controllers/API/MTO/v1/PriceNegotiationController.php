<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Events\NewStack;
use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
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
            foreach ($request['price_negotiations'] as $negotiation) {
                /** @var PriceNegotiation $price_negotiation */
                $price_negotiation = PriceNegotiation::query()->where('uuid', $negotiation['id'])->first();
                if ($price_negotiation) {
                    $price_negotiation->update(['organization_status' => $negotiation['organization_status']]);

                    if (IntegrationUser::where('contr_agent_id', $price_negotiation->contr_agent?->id)->first()?->isProvider()) {
                        event(new NewStack($price_negotiation,
                            (new ProviderSyncStack())->setProvider($price_negotiation->contr_agent),
                        ));
                    }
                    if (IntegrationUser::where('contr_agent_id', $price_negotiation->contr_agent?->id)->first()?->isContractor()) {
                        event(new NewStack($price_negotiation,
                            (new ContractorSyncStack())->setContractor($price_negotiation->contr_agent),
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
