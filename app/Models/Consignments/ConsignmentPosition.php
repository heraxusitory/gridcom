<?php


namespace App\Models\Consignments;


use App\Models\References\Nomenclature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentPosition extends Model
{
    use HasFactory;

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

    public function nomenclature()
    {
        return $this->belongsTo(Nomenclature::class, 'nomenclature_id', 'id');
    }
}
