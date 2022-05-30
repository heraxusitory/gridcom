<?php


namespace App\Services\API\ContrAgents\v1;


use App\Events\NewStack;
use App\Models\IntegrationUser;
use App\Models\Orders\Order;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\Nomenclature;
use App\Models\SyncStacks\MTOSyncStack;
use App\Services\IService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateOrUpdatePriceNegotiationService implements IService
{
    public function __construct(private $data, private IntegrationUser $user)
    {
    }

    public function run()
    {
        foreach ($this->data as $item) {
            DB::transaction(function () use ($item) {
                $position_data = $item['positions'] ?? [];

                if ($item['type'] === PriceNegotiation::TYPE_CONTRACT_WORK())
                    $order = Order::query()->where('uuid', $item['order_id'])->firstOrFail();
                if ($item['type'] === PriceNegotiation::TYPE_CONTRACT_HOME_METHOD())
                    $order = ProviderOrder::query()->where('uuid', $item['order_id'])->firstOrFail();

                $pn_data = collect([
                    'uuid' => $item['id'],
                    'type' => $item['type'],
                    'number' => $item['number'] ?? null,
                    'date' => $item['date'] ?? null,
//                            'organization_status' => PriceNegotiation::ST
                    'creator_contr_agent_id' => $this->user->contr_agent->id,
                    'order_id' => $order->id,
                    'responsible_full_name' => $item['responsible_full_name'] ?? null,
                    'responsible_phone' => $item['responsible_phone'] ?? null,
                    'comment' => $item['comment'] ?? null,
                ]);

                $pn = PriceNegotiation::query()->where('uuid', $pn_data['uuid'])->first();
                if (!is_null($pn))
                    $pn->update($pn_data->toArray());
                else $pn = PriceNegotiation::query()->create(array_merge($pn_data->toArray(), ['organization_status' => PriceNegotiation::ORGANIZATION_STATUS_UNDER_CONSIDERATION]));

                $old_file_url = $pn->file_url;
                if (!is_null($old_file_url)) {
                    Storage::disk('public')->delete($old_file_url);
                }
                $pn->file_url = null;

                if (isset($item['file'])) {
                    $file_link = Storage::disk('public')->putFile('price-negotiations/' . $pn->id, $item['file']);
                    $pn->file_url = $file_link;
                }
                $pn->save();

                $position_ids = [];
                foreach ($position_data as $position) {
                    $nomenclature = Nomenclature::query()
//                        ->where(['uuid' => $position['nomenclature']['id']])
                        ->orWhere(['mnemocode' => $position['nomenclature']['mnemocode']])
                        ->orWhere(['name' => $position['nomenclature']['name']])
                        ->first();

                    $pn_position_data = collect([
                        'position_id' => $position['position_id'],
                        'nomenclature_id' => $nomenclature?->id,
                        'current_price_without_vat' => $position['current_price_without_vat'] ?? null,
                        'new_price_without_vat' => $position['new_price_without_vat'] ?? null,
                    ]);

                    $position = $pn->positions()->updateOrCreate([
                        'position_id' => $position['position_id'],
                    ], $pn_position_data->toArray());
                    $position_ids[] = $position->id;
                }
                $pn->positions()->whereNotIn('id', $position_ids)->delete();

                event(new NewStack($pn, (new MTOSyncStack())));
            });
        }
    }
}
