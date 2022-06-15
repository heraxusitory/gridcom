<?php


namespace App\Http\Controllers\API\MTO\v1\ProviderOrders;


use App\Http\Controllers\Controller;
use App\Models\ProviderOrders\Corrections\OrderCorrection;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\Nomenclature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderCorrectionController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'order_corrections' => 'required|array',
            'order_corrections.*.id' => 'required|uuid',
            'order_corrections.*.provider_order_id' => 'required|uuid',
            'order_corrections.*.date' => 'nullable|date_format:Y-m-d',
            'order_corrections.*.number' => 'nullable|string|max:255',

            'order_corrections.*.common_amount_without_vat' => ['nullable', 'numeric',],
            'order_corrections.*.common_amount_with_vat' => ['nullable', 'numeric',],

            'order_corrections.*.positions' => 'nullable|array',
            'order_corrections.*.positions.*.position_id' => 'required|uuid',
            'order_corrections.*.positions.*.nomenclature_id' => 'required|uuid',
            'order_corrections.*.positions.*.count' => 'nullable|numeric',
            'order_corrections.*.positions.*.price_without_vat' => ['nullable', 'numeric'],
            'order_corrections.*.positions.*.amount_without_vat' => 'nullable|numeric',
            'order_corrections.*.positions.*.vat_rate' => ['nullable', 'numeric', Rule::in(array_keys(config('vat_rates')))],
            'order_corrections.*.positions.*.amount_with_vat' => 'nullable|numeric',
            'order_corrections.*.positions.*.delivery_time' => 'nullable|date_format:Y-m-d',
            'order_corrections.*.positions.*.delivery_address' => 'nullable|string|max:255',
        ]);

        try {
            $data = $request->all()['order_corrections'];

            foreach ($data as $item) {
                DB::transaction(function () use ($item) {
                    $positions = $item['positions'] ?? [];

                    $provider_order = ProviderOrder::query()->firstOrCreate([
                        'uuid' => $item['provider_order_id'],
                    ]);

                    $order_correction = OrderCorrection::query()->updateOrCreate([
                        'correction_id' => $item['id'],
                    ], [
                        'date' => $item['date'] ?? null,
                        'number' => $item['number'] ?? null,
                        'provider_order_id' => $provider_order->id,
                        'common_amount_without_vat' => $item['common_amount_without_vat'],
                        'common_amount_with_vat' => $item['common_amount_with_vat'],
                    ]);

                    $position_ids = [];
                    foreach ($positions as $position) {
                        $nomenclature = Nomenclature::query()->firstOrCreate([
                            'uuid' => $position['nomenclature_id'],
                        ]);

                        $position = $order_correction->positions()->updateOrCreate([
                            'position_id' => $position['position_id'],
                        ], [
                            'nomenclature_id' => $nomenclature->id,
                            'count' => $position['count'] ?? null,
                            'price_without_vat' => $position['price_without_vat'],
                            'amount_without_vat' => $position['amount_without_vat'] ?? null,
                            'amount_with_vat' => $position['amount_with_vat'] ?? null,
                            'vat_rate' => $position['vat_rate'] ?? null,
                            'delivery_time' => $position['delivery_time'] ?? null,
                            'delivery_address' => $position['delivery_address'] ?? null,
                        ]);
                        $position_ids[] = $position->id;
                    }
                    $order_correction->positions()->whereNotIn('id', $position_ids)->delete();
                });
            }
            return response()->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
