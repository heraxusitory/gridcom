<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class NomenclatureToUnit extends Model
{
    protected $table = 'nomenclature_to_unit';

    protected $fillable = [
        'nomenclature_id',
        'unit_id',
    ];
}
