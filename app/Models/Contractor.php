<?php


namespace App\Models;


use App\Models\References\ContactPerson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contractor extends Model
{
    protected $table = 'order_contractors';

    protected $fillable = [
//        'contact_id',
        'contr_agent_id',
        'full_name',
        'email',
        'phone',
        'contractor_responsible_full_name',
        'contractor_responsible_phone',
        'comment',
    ];

    public function contact(): hasOne
    {
        return $this->hasOne(ContactPerson::class, 'id', 'contact_id');
    }
}
