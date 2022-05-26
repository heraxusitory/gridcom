<?php


namespace App\Transformers\API\ContrAgents\v1;


use App\Models\ProviderOrders\Positions\BaseProviderOrderPosition;
use League\Fractal\TransformerAbstract;

class BaseProviderOrderPositionTransformer extends TransformerAbstract
{
    public function transform(BaseProviderOrderPosition $position)
    {
        return [
            'position_id' => $position->position_id,
            'provider_order_id' => $position->provider_order_id,
            'nomenclature' => [
                'name' => $position->nomenclature?->name,
                'mnemocode' => $position->nomenclature?->mnemocode,
            ],
            'count' => $position->count,
            'price_without_vat' => $position->price_without_vat,
            'amount_without_vat' => $position->amount_without_vat,
            'vat_rate' => $position->vat_rate,
            'amount_with_vat' => $position->amount_with_vat,
            'delivery_time' => $position->delivery_time,
            'delivery_address' => $position->delivery_address,
            'organization_comment' => $position->organization_comment,
        ];
    }
}
