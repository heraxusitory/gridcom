<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class NomenclatureUnit extends Model
{
    protected $table = 'nomenclature_units';

    protected $fillable = [
        'uuid',
        'name',
        'is_visible_to_client',
    ];

    public function nomenclatures()
    {
        return $this->belongsToMany(Nomenclature::class, 'nomenclature_to_unit', 'unit_id', 'nomenclature_id');
    }
}
