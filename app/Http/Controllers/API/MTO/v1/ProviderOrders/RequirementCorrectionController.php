<?php


namespace App\Http\Controllers\API\MTO\v1\ProviderOrders;


use App\Http\Controllers\Controller;
use App\Models\ProviderOrders\Corrections\RequirementCorrection;
use App\Models\ProviderOrders\Corrections\RequirementCorrectionPosition;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\Nomenclature;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\RequirementCorrectionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RequirementCorrectionController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'requirement_corrections' => 'required|array',
            'requirement_corrections.*.id' => 'required|uuid',
            'requirement_corrections.*.provider_order_id' => 'required|uuid',
            'requirement_corrections.*.date' => 'nullable|date_format:Y-m-d',
            'requirement_corrections.*.number' => 'nullable|string|max:255',
            'requirement_corrections.*.provider_status' => ['required', 'string', Rule::in(RequirementCorrection::getProviderStatuses())],

            'requirement_corrections.*.positions' => 'nullable|array',
            'requirement_corrections.*.positions.*.position_id' => 'required|uuid',
            'requirement_corrections.*.positions.*.status' => ['nullable', Rule::in(RequirementCorrectionPosition::getStatuses())],
            'requirement_corrections.*.positions.*.nomenclature_id' => ['required', 'uuid'],
            'requirement_corrections.*.positions.*.count' => ['nullable', 'numeric'],
            'requirement_corrections.*.positions.*.price_without_vat' => ['nullable', 'numeric'],
            'requirement_corrections.*.positions.*.amount_without_vat' => ['nullable', 'numeric'],
            'requirement_corrections.*.positions.*.amount_with_vat' => ['nullable', 'numeric'],
            'requirement_corrections.*.positions.*.vat_rate' => ['nullable', Rule::in(array_keys(config('vat_rates')))],
            'requirement_corrections.*.positions.*.delivery_time' => ['nullable', 'date_format:Y-m-d'],
            'requirement_corrections.*.positions.*.delivery_address' => ['nullable', 'string', 'max:255'],
            'requirement_corrections.*.positions.*.organization_comment' => ['nullable', 'string'],
        ]);

        try {
            $data = $request->all()['requirement_corrections'];

            foreach ($data as $item) {
                DB::transaction(function () use ($item) {
                    $positions = $item['positions'] ?? [];

                    $provider_order = ProviderOrder::query()->firstOrCreate([
                        'uuid' => $item['provider_order_id'],
                    ]);

                    /** @var RequirementCorrection $requirement_correction */
                    $requirement_correction = RequirementCorrection::query()->updateOrCreate([
                        'correction_id' => $item['id'],
                    ], [
                        'date' => $item['date'] ?? null,
                        'number' => $item['number'] ?? null,
                        'provider_order_id' => $provider_order->id,
                        'provider_status' => $item['provider_status'],
                    ]);

                    $position_ids = [];
                    foreach ($positions as $position) {
                        $nomenclature = Nomenclature::query()->firstOrCreate([
                            'uuid' => $position['nomenclature_id'],
                        ]);

                        $position = $requirement_correction->positions()->updateOrCreate([
                            'position_id' => $position['position_id'],
                        ], [
                            'nomenclature_id' => $nomenclature->id,
                            'status' => $position['status'] ?? null,
                            'count' => $position['count'] ?? null,
                            'price_without_vat' => $position['price_without_vat'] ?? null,
                            'amount_without_vat' => $position['amount_without_vat'] ?? null,
                            'amount_with_vat' => $position['amount_with_vat'] ?? null,
                            'vat_rate' => $position['vat_rate'] ?? null,
                            'delivery_time' => $position['delivery_time'] ?? null,
                            'delivery_address' => $position['delivery_address'] ?? null,
                            'organization_comment' => $position['organization_comment'] ?? null,
                        ]);
                        $position_ids[] = $position->id;
                    }
                    $requirement_correction->positions()->whereNotIn('id', $position_ids)->delete();
                });
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
                $requirement_corrections = RequirementCorrection::query()
                    ->with([
                        'positions',
                    ])
                    /*->where('sync_required', true)*/ #todo: расскомментировать в будущем
                    ->get();
//                RequirementCorrection::query()->whereIn('id', $orders->pluck('id'))->update(['sync_required' => false]);#todo: расскомментировать в будущем
                return fractal()->collection($requirement_corrections)->transformWith(RequirementCorrectionTransformer::class)->serializeWith(CustomerSerializer::class);
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
                $count = RequirementCorrection::query()
                    ->whereIn('correction_id', $request->ids)
                    ->update(['sync_required' => true]);
                return response()->json('В очередь поставлено ' . $count . ' корректировок потребности');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
