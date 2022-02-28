<?php


namespace App\Models;


use App\Models\References\ContactPerson;
use App\Models\References\ProviderContractDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Provider extends Model
{
    protected $table = 'providers';

    protected $fillable = [
        'provider_contract_id',
        'contact_id',
    ];

    public function contract(): hasOne
    {
        return $this->hasOne(ProviderContractDocument::class, 'provider_contract_id', 'id');
    }

    public function contact(): hasOne
    {
        return $this->hasOne(ContactPerson::class, 'contact_id', 'id');
    }
}
