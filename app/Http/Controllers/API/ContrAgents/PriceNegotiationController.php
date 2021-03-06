<?php


namespace App\Http\Controllers\API\ContrAgents;


use App\Http\Controllers\Controller;
use App\Models\IntegrationUser;
use App\Models\Orders\Order;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Models\User;
use App\Serializers\CustomerSerializer;
use App\Services\API\ContrAgents\v1\CreateOrUpdatePriceNegotiationService;
use App\Transformers\API\ContrAgents\v1\PriceNegotiationTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PriceNegotiationController extends Controller
{
    public function sync(Request $request)
    {
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();

//        if ($user->isProvider()) {
//            $types = PriceNegotiation::TYPE_CONTRACT_HOME_METHOD();
//        } else if ($user->isContractor()) {
//            $types = PriceNegotiation::TYPE_CONTRACT_WORK();
//        }
        $data = $request->all();
        Validator::make($data, [
            'price_negotiations' => 'required|array',
            'price_negotiations.*.id' => 'required|uuid',
//            'price_negotiations.*.organization_status' => ['required', Rule::in/(PriceNegotiation::getOrganizationStatuses())]
            'price_negotiations.*.type' => ['required', Rule::in(PriceNegotiation::TYPES())],
            'price_negotiations.*.number' => ['required', 'string', 'max:255'],
            'price_negotiations.*.date' => ['required', 'date_format:Y-m-d'],
            'price_negotiations.*.order_id' => ['required', 'uuid'],
            'price_negotiations.*.responsible_full_name' => ['nullable', 'string', 'max:255'],
            'price_negotiations.*.responsible_phone' => ['nullable', 'string', 'max:255'],
            'price_negotiations.*.comment' => ['nullable', 'string', 'max:255'],
            'price_negotiations.*.file' => ['nullable', 'file'],
        ])->validate();

        $validator = Validator::make($data, [
            'price_negotiations.*.positions' => ['required', 'array'],
            'price_negotiations.*.positions.*' => ['required'],
            'price_negotiations.*.positions.*.position_id' => ['required', 'uuid'],
            'price_negotiations.*.positions.*.nomenclature.mnemocode' => ['required', 'string', 'max:255'],
            'price_negotiations.*.positions.*.nomenclature.name' => ['required', 'string', 'max:255'],
            'price_negotiations.*.positions.*.current_price_without_vat' => ['required', 'numeric'],
            'price_negotiations.*.positions.*.new_price_without_vat' => ['required', 'numeric'],
        ]);

        Log::debug('data', [$data]);
        $validator->after(function ($validator) use ($data) {
            foreach ($data['price_negotiations'] as $key => $item) {
                if ($item['type'] === PriceNegotiation::TYPE_CONTRACT_WORK()) {
                    if (is_null(Order::query()->where('uuid', $item['order_id'])->first())) {
                        $validator->errors()->add('price_negotiations.' . $key . '.order_id', 'The price_negotiations.' . $key . '.order_id is invalid');
                        break;
                    }
                }
                if ($item['type'] === PriceNegotiation::TYPE_CONTRACT_HOME_METHOD()) {
                    if (is_null(ProviderOrder::query()->where('uuid', $item['order_id'])->first())) {
                        $validator->errors()->add('price_negotiations.' . $key . '.order_id', 'The price_negotiations.' . $key . '.order_id is invalid');
                        break;
                    }
                }
            }
        })->validate();

        try {
            $data = $data['price_negotiations'];
            (new CreateOrUpdatePriceNegotiationService($data, $user))->run();
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
                    $pr = ContractorSyncStack::getModelEntities(PriceNegotiation::class, $user->contr_agent);
                else if ($user->isProvider())
                    $pr = ProviderSyncStack::getModelEntities(PriceNegotiation::class, $user->contr_agent);
                else $pr = [];
                return fractal()->collection($pr)->transformWith(PriceNegotiationTransformer::class)->serializeWith(CustomerSerializer::class);
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
                return response()->json('???? ?????????? ?????????????? ' . $count . ' ???????????????????????? ??????');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function downloadFile(Request $request, $price_negotiation_id)
    {
        /** @var IntegrationUser $user */
        $user = Auth::guard('api')->user();
        try {
            $price_negotiation = PriceNegotiation::query()->where(['uuid' => $price_negotiation_id, 'creator_contr_agent_id' => $user->contr_agent?->id])->firstOrFail();
            if (Storage::exists($price_negotiation->file_url)) {
                return response()->download(Storage::path($price_negotiation->file_url));
            }
            return response('', 204);
        } catch
        (ModelNotFoundException $e) {
            return response()->json(['message' => '???? ?????????????? ?????????????? ???????????????????????? ??????.'], 404);
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
