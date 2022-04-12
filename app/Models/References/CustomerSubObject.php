<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class CustomerSubObject extends Model
{
    protected $table = 'customer_sub_objects';

    protected $fillable = [
        'uuid',
        'customer_object_id',
        'name',
        'is_confirmed',
    ];
}
