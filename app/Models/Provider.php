<?php


namespace App\Models;


use App\Models\References\ContactPerson;
use App\Models\References\ProviderContractDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Provider extends Model
{
    protected $table = 'order_providers';

    protected $fillable = [
        'provider_contract_id',
//        'contact_id',
        'contr_agent_id',
        'full_name',
        'email',
        'phone',
        'rejected_comment',
        'agreed_comment',
    ];

    public function contract(): hasOne
    {
        return $this->hasOne(ProviderContractDocument::class, 'id', 'provider_contract_id');
    }

    public function contact(): hasOne
    {
        return $this->hasOne(ContactPerson::class, 'id', 'contact_id');
    }

    public function provider_contract()
    {
        return $this->hasOne(ProviderContractDocument::class, 'id', 'provider_contract_id');
    }
}
