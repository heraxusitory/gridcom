<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class ProviderContractDocument extends Model
{
    protected $table = 'provider_contracts';

    protected $fillable = [
        'provider_contract',
        'provider_contract_date',
    ];
}
