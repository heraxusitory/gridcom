<?php


namespace App\Models\ProviderOrders\Corrections;


use App\Models\ProviderOrders\ProviderOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OrderCorrection extends Model
{
    protected $table = 'order_corrections';

    protected $fillable = [
        'provider_order_id',
        'correction_id',
        'date',
        'number',
    ];

    public function positions()
    {
        return $this->hasMany(OrderCorrectionPosition::class, 'order_correction_id', 'id');
    }

    public function provider_order()
    {
        return $this->belongsTo(ProviderOrder::class, 'provider_order_id', 'id');
    }

    public function getDateAttribute($value)
    {
        return (new Carbon($value))->format('Y-m-d');
    }
}
