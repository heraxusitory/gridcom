<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class Nomenclature extends Model
{
    protected $table = 'nomenclature';

    protected $fillable = [
        'mnemocode',
        'name',
    ];

    public function units()
    {
        return $this->belongsToMany(NomenclatureUnit::class, 'nomenclature_to_unit', 'unit_id', 'nomenclature_id');
    }
}
