<?php


namespace App\Http\Controllers\API\MTO\v1\ProviderOrders;


use App\Events\NewStack;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\ContrAgent;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Serializers\CustomerSerializer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProviderOrderController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'provider_orders' => 'required|array',
            'provider_orders.*.id' => 'required|uuid',
            'provider_orders.*.number' => 'required|string|max:255',
            'provider_orders.*.order_date' => 'nullable|date_format:Y-m-d',
            'provider_orders.*.contract_number' => 'nullable|string|max:255',
            'provider_orders.*.contract_date' => 'nullable|string|max:255',
            'provider_orders.*.contract_stage' => ['nullable', 'string', /*Rule::in(ProviderOrder::STAGES())*/],
            'provider_orders.*.provider_contr_agent_id' => ['required', 'uuid'],
            'provider_orders.*.organization_id' => ['required', 'uuid'],
            'provider_orders.*.responsible_full_name' => ['nullable', 'string', 'max:255'],
            'provider_orders.*.responsible_phone' => ['nullable', 'string', 'max:255'],
            'provider_orders.*.organization_comment' => ['nullable', 'string',],

            'provider_orders.*.common_amount_without_vat_in_base_positions' => ['nullable', 'numeric',],
            'provider_orders.*.common_amount_with_vat_in_base_positions' => ['nullable', 'numeric',],
            'provider_orders.*.common_amount_without_vat_in_actual_positions' => ['nullable', 'numeric',],
            'provider_orders.*.common_amount_with_vat_in_actual_positions' => ['nullable', 'numeric',],

            'provider_orders.*.base_positions' => ['nullable', 'array',],
            'provider_orders.*.base_positions.*.position_id' => ['required', 'uuid'],
            'provider_orders.*.base_positions.*.nomenclature_id' => ['required', 'uuid'],
            'provider_orders.*.base_positions.*.count' => ['nullable', 'numeric'],
            'provider_orders.*.base_positions.*.price_without_vat' => ['nullable', 'numeric'],
            'provider_orders.*.base_positions.*.amount_without_vat' => ['nullable', 'numeric'],
            'provider_orders.*.base_positions.*.amount_with_vat' => ['nullable', 'numeric'],
            'provider_orders.*.base_positions.*.vat_rate' => ['nullable', Rule::in(array_keys(config('vat_rates')))],
            'provider_orders.*.base_positions.*.delivery_time' => ['nullable', 'date_format:Y-m-d'],
            'provider_orders.*.base_positions.*.delivery_address' => ['nullable', 'string', 'max:255'],
            'provider_orders.*.base_positions.*.organization_comment' => ['nullable', 'string'],

            'provider_orders.*.actual_positions' => ['nullable', 'array',],
            'provider_orders.*.actual_positions.*.position_id' => ['required', 'uuid',],
            'provider_orders.*.actual_positions.*.nomenclature_id' => ['required', 'uuid',],
            'provider_orders.*.actual_positions.*.count' => ['nullable', 'numeric',],
            'provider_orders.*.actual_positions.*.price_without_vat' => ['nullable', 'numeric',],
            'provider_orders.*.actual_positions.*.amount_with_vat' => ['nullable', 'numeric',],
            'provider_orders.*.actual_positions.*.amount_without_vat' => ['nullable', 'numeric',],
            'provider_orders.*.actual_positions.*.vat_rate' => ['nullable', Rule::in(array_keys(config('vat_rates')))],
            'provider_orders.*.actual_positions.*.delivery_time' => ['nullable', 'date_format:Y-m-d'],
            'provider_orders.*.actual_positions.*.delivery_address' => ['nullable', 'string', 'max:255'],
            'provider_orders.*.actual_positions.*.organization_comment' => ['nullable', 'string'],
        ]);

        try {
            $data = $request->all()['provider_orders'];

            foreach ($data as $item) {
                DB::transaction(function () use ($item) {
                    $base_positions = $item['base_positions'] ?? [];
                    $actual_positions = $item['actual_positions'] ?? [];

                    $provider_contr_agent = ContrAgent::query()->firstOrCreate([
                        'uuid' => $item['provider_contr_agent_id'],
                    ]);
                    $organization = Organization::query()->firstOrCreate([
                        'uuid' => $item['organization_id'],
                    ]);

                    /** @var ProviderOrder $provider_order */
                    $provider_order = ProviderOrder::query()->updateOrCreate([
                        'uuid' => $item['id'],
                    ], [
                        'number' => $item['number'] ?? null,
                        'order_date' => $item['order_date'] ?? null,
                        'contract_number' => $item['contract_number'] ?? null,
                        'contract_date' => $item['contract_date'] ?? null,
                        'contract_stage' => $item['contract_stage'],
                        'provider_contr_agent_id' => $provider_contr_agent->id,
                        'organization_id' => $organization->id,
                        'responsible_full_name' => $item['responsible_full_name'] ?? null,
                        'responsible_phone' => $item['responsible_phone'] ?? null,
                        'organization_comment' => $item['organization_comment'] ?? null,
                        'common_amount_without_vat_in_base_positions' => $item['common_amount_without_vat_in_base_positions'] ?? null,
                        'common_amount_with_vat_in_base_positions' => $item['common_amount_with_vat_in_base_positions'] ?? null,
                        'common_amount_without_vat_in_actual_positions' => $item['common_amount_without_vat_in_actual_positions'] ?? null,
                        'common_amount_with_vat_in_actual_positions' => $item['common_amount_with_vat_in_actual_positions'] ?? null,
                    ]);

                    $base_position_ids = [];
                    foreach ($base_positions as $base_position) {
                        $nomenclature = Nomenclature::query()->firstOrCreate([
                            'uuid' => $base_position['nomenclature_id'],
                        ]);
                        $base_position = $provider_order->base_positions()->updateOrCreate([
                            'position_id' => $base_position['position_id'],
                        ], [
                            'nomenclature_id' => $nomenclature->id,
                            'count' => $base_position['count'] ?? null,
                            'price_without_vat' => $base_position['price_without_vat'] ?? null,
                            'amount_without_vat' => $base_position['amount_without_vat'] ?? null,
                            'amount_with_vat' => $base_position['amount_with_vat'] ?? null,
                            'vat_rate' => $base_position['vat_rate'] ?? null,
                            'delivery_time' => $base_position['delivery_time'] ?? null,
                            'delivery_address' => $base_position['delivery_address'] ?? null,
                            'organization_comment' => $base_position['organization_comment'] ?? null,
                        ]);
                        $base_position_ids[] = $base_position->id;
                    }
                    $provider_order->base_positions()->whereNotIn('id', $base_position_ids)->delete();


                    $actual_position_ids = [];
                    foreach ($actual_positions as $actual_position) {
                        $nomenclature = Nomenclature::query()->firstOrCreate([
                            'uuid' => $actual_position['nomenclature_id'],
                        ]);
                        $actual_position = $provider_order->actual_positions()->updateOrCreate([
                            'position_id' => $actual_position['position_id'],
                        ], [
                            'nomenclature_id' => $nomenclature->id,
                            'count' => $actual_position['count'] ?? null,
                            'price_without_vat' => $actual_position['price_without_vat'] ?? null,
                            'amount_without_vat' => $actual_position['amount_without_vat'] ?? null,
                            'amount_with_vat' => $actual_position['amount_with_vat'] ?? null,
                            'vat_rate' => $actual_position['vat_rate'] ?? null,
                            'delivery_time' => $actual_position['delivery_time'] ?? null,
                            'delivery_address' => $actual_position['delivery_address'] ?? null,
                            'organization_comment' => $actual_position['organization_comment'] ?? null,
                        ]);
                        $actual_position_ids[] = $actual_position->id;
                    }
                    $provider_order->actual_positions()->whereNotIn('id', $actual_position_ids)->delete();

                    event(new NewStack($provider_order,
                            (new ProviderSyncStack())->setProvider($provider_order->provider))
                    );
                });
            }
            return response()->json();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
