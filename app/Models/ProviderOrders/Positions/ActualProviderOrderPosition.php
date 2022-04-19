<?php


namespace App\Models\ProviderOrders\Positions;


use App\Models\References\Nomenclature;
use Illuminate\Database\Eloquent\Model;

class ActualProviderOrderPosition extends Model
{
    protected $table = 'actual_provider_order_positions';

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

    public function nomenclature()
    {
        return $this->hasOne(Nomenclature::class, 'id', 'nomenclature_id');
    }
}