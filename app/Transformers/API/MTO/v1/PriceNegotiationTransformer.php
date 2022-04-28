<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\PriceNegotiations\PriceNegotiation;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class PriceNegotiationTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'positions',
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(PriceNegotiation $price_negotiation)
    {
        return [
            'id' => $price_negotiation?->uuid,
            'type' => $price_negotiation?->type,
            'number' => $price_negotiation?->number,
            'date' => $price_negotiation?->date,
            'organization_status' => $price_negotiation?->organization_status,
            'order_id' => optional($price_negotiation->order()->first())->uuid,
            'responsible_full_name' => $price_negotiation?->responsible_full_name,
            'responsible_phone' => $price_negotiation?->responsible_phone,
            'comment' => $price_negotiation?->comment,
            'file_url' => $price_negotiation?->file_url ? Storage::disk('public')->url($price_negotiation->file_url) : $price_negotiation?->file_url,
        ];
    }

    public function includePositions(PriceNegotiation $price_negotiation)
    {
        if (optional($price_negotiation)->positions)
            return $this->collection($price_negotiation->positions, new PriceNegotiationPositionTransformer());
        return $this->null();
    }
}
