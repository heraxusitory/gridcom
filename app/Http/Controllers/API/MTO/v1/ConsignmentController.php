<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Http\Controllers\Controller;
use App\Models\Consignments\Consignment;
use App\Models\Orders\Order;
use App\Models\References\Nomenclature;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConsignmentController extends Controller
{
    public function sync(Request $request)
    {
        Validator::validate($request->all(), [
            'consignments' => 'required|array',
            'consignments.*.id' => 'required|uuid',
            'consignments.*.number' => 'required|string|max:255',
            'consignments.*.date' => 'required|date_format:d.m.Y',
            'consignments.*.order_id' => 'required|uuid|exists:orders,uuid',
            'consignments.*.responsible_full_name' => 'required|string|max:255',
            'consignments.*.responsible_phone' => 'required|string|max:255',
            'consignments.*.comment' => 'required|string',

            'consignments.*.positions' => 'required|array',
            'consignments.*.positions.*.id' => 'required|uuid',
            'consignments.*.positions.*.nomenclature_id' => 'required|uuid',
            'consignments.*.positions.*.count' => 'required|numeric',
            'consignments.*.positions.*.price_without_vat' => 'required|numeric',
            'consignments.*.positions.*.amount_without_vat' => 'required|numeric',
            'consignments.*.positions.*.vat_rate' => 'required|numeric',
            'consignments.*.positions.*.amount_with_vat' => 'required|numeric',
            'consignments.*.positions.*.country' => ['required', 'string', Rule::in(array_keys(config('countries')))],
            'consignments.*.positions.*.cargo_custom_declaration' => 'required|string',
            'consignments.*.positions.*.declaration' => 'required|string',
        ]);

        try {
            $data = $request->all()['consignments'];

            foreach ($data as $item) {
                DB::transaction(function () use ($item) {
                    $position_data = $item['positions'];
                    $order = Order::query()->where('uuid', $item['order_id'])->firstOrFail();

                    $consignment = Consignment::query()->updateOrCreate([
                        'uuid' => $item['id'],
                    ], [
                        'number' => $item['number'],
                        'date' => (new Carbon($item['date']))->format('d.m.Y'),
                        'order_id' => $order->id,
                        'responsible_full_name' => $item['responsible_full_name'],
                        'responsible_phone' => $item['responsible_phone'],
                        'comment' => $item['comment'],
                    ]);

                    $position_ids = [];
                    foreach ($position_data as $position) {
                        $nomenclature = Nomenclature::query()->firstOrCreate([
                            'uuid' => $position['nomenclature_id'],
                        ]);
                        $position = $consignment->positions()->updateOrCreate([
                            'position_id' => $position['id'],
                        ],
                            [
                                'nomenclature_id' => $nomenclature->id,
                                'count' => $position['count'],
                                'price_without_vat' => $position['price_without_vat'],
                                'amount_without_vat' => $position['amount_without_vat'],
                                'vat_rate' => $position['vat_rate'],
                                'amount_with_vat' => $position['amount_with_vat'],
                                'country' => $position['country'],
                                'cargo_custom_declaration' => $position['cargo_custom_declaration'],
                                'declaration' => $position['declaration'],
                            ]);
                        $position_ids[] = $position->id;
                    }
                    $consignment->positions()->whereNotIn('id', $position_ids)->delete();
                });
            }
            return response()->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
