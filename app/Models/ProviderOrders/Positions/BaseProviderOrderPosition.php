<?php


namespace App\Models\ProviderOrders\Positions;


use Illuminate\Database\Eloquent\Model;

class BaseProviderOrderPosition extends Model
{
    protected $table = 'base_provider_order_positions';

    protected $fillable = [
        'position_id',
        'provider_order_id',
        'nomenclature_id',
        'count',
        'price_without_vat',
        'amount_without_vat',
        'vat_rate',
        'amount_with_vat',
        'delivery_time',
        'delivery_address',
        'organization_comment',
    ];
}
