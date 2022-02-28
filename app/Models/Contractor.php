<?php


namespace App\Models;


use App\Models\References\ContactPerson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contractor extends Model
{
    protected $table = 'order_contractors';

    protected $fillable = [
        'contact_id',
        'contractor_responsible_full_name',
        'contractor_responsible_phone',
    ];

    public function contact(): hasOne
    {
        return $this->hasOne(ContactPerson::class, 'contact_id', 'id');
    }
}
