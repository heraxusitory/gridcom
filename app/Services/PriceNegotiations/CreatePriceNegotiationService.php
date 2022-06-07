<?php


namespace App\Services\PriceNegotiations;


use App\Events\NewStack;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Models\User;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CreatePriceNegotiationService implements IService
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct(private Request $payload)
    {
        $this->user = auth('webapi')->user();
    }

    public function run()
    {
        $data = $this->payload->all();

        $organization_status = match ($data['action']) {
            PriceNegotiation::ACTION_DRAFT() => PriceNegotiation::ORGANIZATION_STATUS_DRAFT,
            PriceNegotiation::ACTION_APPROVE() => PriceNegotiation::ORGANIZATION_STATUS_UNDER_CONSIDERATION,
            default => throw new BadRequestException('Action is required', 400),
        };

        return DB::transaction(function () use ($data, $organization_status) {
            /** @var PriceNegotiation $price_negotiation */
            $price_negotiation = PriceNegotiation::query()->create([
                'uuid' => Str::uuid(),
                'type' => $data['type'],
                'organization_status' => $organization_status,
                'order_id' => $data['order_id'],
                'creator_contr_agent_id' => $this->user->contr_agent_id(),
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
                'date' => Carbon::today()->format('Y-m-d'),
                'file_url' => $file_link ?? null,
            ]);

            foreach ($data['positions'] as $position) {
                $price_negotiation->positions()->create([
                    'position_id' => Str::uuid(),
                    'nomenclature_id' => $position['nomenclature_id'],
                    'current_price_without_vat' => $position['current_price_without_vat'],
                    'new_price_without_vat' => $position['new_price_without_vat'],
                ]);
            }
            if (isset($data['file'])) {
                Log::debug('data_file', [$data['file']]);
                Log::debug('data_file2', [$this->payload->file('file')]);
                $file_link = Storage::putFile('price-negotiations/' . $price_negotiation->id, /*$data['file']*/ $this->payload->file('file'));
                $price_negotiation->file_url = /*Storage::disk('public')->*//*url*/
                    $file_link;
                $price_negotiation->save();
            }

            if ($this->user->isProvider())
                event(new NewStack($price_negotiation,
                        (new ProviderSyncStack())->setProvider($this->user->contr_agent_id()))
                );
            if ($this->user->isContractor())
                event(new NewStack($price_negotiation,
                        (new ContractorSyncStack())->setContractor($this->user->contr_agent_id()))
                );

            event(new NewStack($price_negotiation,
                    new MTOSyncStack())
            );

            return $price_negotiation;
        });
    }

}
