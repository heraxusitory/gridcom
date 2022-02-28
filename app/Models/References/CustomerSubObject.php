<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class CustomerSubObject extends Model
{
    protected $table = 'customer_sub_objects';

    protected $fillable = [
        'customer_object_id',
        'name',
    ];
}
