<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class Nomenclature extends Model
{
    protected $table = 'nomenclature';

    protected $fillable = [
        'mnemocode',
        'nomenclature',
        'unit',
    ];
}
