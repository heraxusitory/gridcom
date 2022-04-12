<?php


namespace App\Models\References;


use App\Models\Orders\OrderPositions\OrderPosition;
use Illuminate\Database\Eloquent\Model;

class Nomenclature extends Model
{
    protected $table = 'nomenclature';

    protected $fillable = [
        'uuid',
        'mnemocode',
        'name',
        'price',
        'is_confirmed',
    ];

    public function units()
    {
        return $this->belongsToMany(NomenclatureUnit::class, 'nomenclature_to_unit', 'unit_id', 'nomenclature_id');
    }
}
