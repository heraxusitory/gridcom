<?php


namespace App\Models\Consignments;


use App\Models\Orders\Order;
use App\Models\References\Nomenclature;
use App\Traits\Filterable;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ConsignmentPosition extends Model
{
    use HasFactory, Filterable, Sortable;

    protected $table = 'consignment_positions';

    protected $fillable = [
        'position_id',
        'order_id',
        'consignment_id',
        'nomenclature_id',
        'count',
        'price_without_vat',
        'amount_without_vat',
        'vat_rate',
        "amount_with_vat",
        'country',
        'cargo_custom_declaration',
        'declaration',
    ];

    protected $casts = [
        'count' => 'float',
        'price_without_vat' => 'float',
        'amount_without_vat' => 'float',
        'vat_rate' => 'float',
        'amount_with_vat' => 'float'
    ];

    protected $with = [
        'nomenclature',
    ];

    public function nomenclature()
    {
        return $this->belongsTo(Nomenclature::class, 'nomenclature_id', 'id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function consignment()
    {
        return $this->belongsTo(Consignment::class, 'consignment_id', 'id');
    }
}
