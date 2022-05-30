<?php


namespace App\Services\PriceNegotiations;


use App\Models\PriceNegotiations\PriceNegotiation;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CreatePriceNegotiationService implements IService
{
    public function __construct(private $payload)
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
            $price_negotiation = PriceNegotiation::query()->create([
                'uuid' => Str::uuid(),
                'type' => $data['type'],
                'organization_status' => $organization_status,
                'order_id' => $data['order_id'],
                'creator_contr_agent_id' => $this->user->contr_agent->id,
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
                $file_link = Storage::disk('public')->putFile('price-negotiations/' . $price_negotiation->id, $data['file']);
                $price_negotiation->file_url = /*Storage::disk('public')->*//*url*/
                    $file_link;
                $price_negotiation->save();
            }
            return $price_negotiation;
        });
    }

}
