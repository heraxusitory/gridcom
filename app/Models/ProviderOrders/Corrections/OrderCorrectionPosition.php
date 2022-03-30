<?php


namespace App\Models\ProviderOrders\Corrections;


use Illuminate\Database\Eloquent\Model;

class OrderCorrectionPosition extends Model
{
    protected $table = 'order_correction_positions';

    protected $fillable = [
        'position_id',
        'order_correction_id',
        'nomenclature_id',
        'count',
        'amount_without_vat',
        'vat_rate',
        'amount_with_vat',
        'delivery_time',
        'delivery_address',
    ];
}
