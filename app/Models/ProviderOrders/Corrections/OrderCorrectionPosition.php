<?php


namespace App\Models\ProviderOrders\Corrections;


use App\Models\References\Nomenclature;
use App\Traits\Filterable;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OrderCorrectionPosition extends Model
{
    use Filterable, Sortable;

    protected $table = 'order_correction_positions';

    protected $fillable = [
        'position_id',
        'order_correction_id',
        'nomenclature_id',
        'count',
        'amount_without_vat',
        'price_without_vat',
        'vat_rate',
        'amount_with_vat',
        'delivery_time',
        'delivery_address',
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
