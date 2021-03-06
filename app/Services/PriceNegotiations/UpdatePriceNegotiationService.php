<?php


namespace App\Services\PriceNegotiations;


use App\Events\NewStack;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdatePriceNegotiationService implements IService
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct(private Request $payload, private PriceNegotiation $price_negotiation)
    {
        $this->user = auth('webapi')->user();
    }

    public function run()
    {
        $data = $this->payload;

        $organization_status = match ($data['action']) {
            PriceNegotiation::ACTION_DRAFT() => PriceNegotiation::ORGANIZATION_STATUS_DRAFT,
            PriceNegotiation::ACTION_APPROVE() => PriceNegotiation::ORGANIZATION_STATUS_UNDER_CONSIDERATION,
            default => throw new BadRequestException('Action is required', 400),
        };

        return DB::transaction(function () use ($data, $organization_status) {
            $this->price_negotiation->update([
                'type' => $data['type'],
                'organization_status' => $organization_status,
                'order_id' => $data['order_id'],
                'creator_contr_agent_id' => $this->user->contr_agent_id(),
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
                'date' => Carbon::today()->format('Y-m-d'),
            ]);

            $position_ids = [];
            foreach ($data['positions'] as $position) {
                $position = $this->price_negotiation->positions()->updateOrCreate([
                    'position_id' => $position['position_id'] ?? null,
                ], [
                    'position_id' => $position['position_id'] ?? Str::uuid(),
                    'nomenclature_id' => $position['nomenclature_id'],
                    'current_price_without_vat' => $position['current_price_without_vat'],
                    'new_price_without_vat' => $position['new_price_without_vat'],
                ]);
                $position_ids[] = $position->id;
            }
            $this->price_negotiation->positions()->whereNotIn('id', $position_ids)->delete();

            $old_file_url = $this->price_negotiation->file_url;
            if (!is_null($old_file_url)) {
                Storage::delete($old_file_url);
            }
            $this->price_negotiation->file_url = null;

            if (isset($data['file'])) {
                Log::debug('data_file', [$data['file']]);
                Log::debug('data_file2', [$this->payload->file('file')]);
                $file_link = Storage::putFile('price-negotiations/' . $this->price_negotiation->id, $this->payload->file('file')/*$data['file']*/);
                $this->price_negotiation->file_url = $file_link;
            }
            $this->price_negotiation->save();

            if ($this->price_negotiation->organization_status !== PriceNegotiation::ORGANIZATION_STATUS_DRAFT) {
                if ($this->user->isProvider())
                    event(new NewStack($this->price_negotiation,
                            (new ProviderSyncStack())->setProvider($this->user->contr_agent()))
                    );
                if ($this->user->isContractor())
                    event(new NewStack($this->price_negotiation,
                            (new ContractorSyncStack())->setContractor($this->user->contr_agent()))
                    );

                event(new NewStack($this->price_negotiation,
                        new MTOSyncStack())
                );
            }

            return $this->price_negotiation;
        });
    }
}
