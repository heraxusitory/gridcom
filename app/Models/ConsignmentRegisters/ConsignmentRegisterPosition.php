<?php


namespace App\Models\ConsignmentRegisters;


use App\Models\Consignments\Consignment;
use App\Models\References\Nomenclature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentRegisterPosition extends Model
{
    use HasFactory;

    protected $table = 'consignment_register_positions';

    protected $fillable = [
        'position_id',
        'consignment_register_id',
        'consignment_id',
        'nomenclature_id',
        'price_without_vat',
        'amount_without_vat',
        'count',
        'vat_rate',
        'amount_with_vat',
        'result_status',
    ];

    public function nomenclature()
    {
        return $this->hasOne(Nomenclature::class, 'id', 'nomenclature_id');
    }

    public function consignment()
    {
        return $this->hasOne(Consignment::class, 'id', 'consignment_id');
    }
}
