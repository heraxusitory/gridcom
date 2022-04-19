<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerObject extends Model
{
    protected $table = 'customer_objects';

    protected $fillable = [
        'uuid',
        'name',
        'is_visible_to_client',
    ];

    public function subObjects(): HasMany
    {
        return $this->hasMany(CustomerSubObject::class, 'customer_object_id', 'id');
    }
}
