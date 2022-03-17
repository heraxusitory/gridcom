<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organizations';

    protected $fillable = [
        'uuid',
        'name',
    ];
}
