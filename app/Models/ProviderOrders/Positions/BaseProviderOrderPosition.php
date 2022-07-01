<?php


namespace App\Models\ProviderOrders\Positions;


use App\Models\References\Nomenclature;
use App\Traits\Filterable;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class BaseProviderOrderPosition extends Model
{
    use Filterable, Sortable;

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

    protected $with = [
        'nomenclature',
    ];

    public function getDeliveryTimeAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }

    public function nomenclature()
    {
        return $this->hasOne(Nomenclature::class, 'id', 'nomenclature_id');
    }
}
