<?php


namespace App\Models\ContrAgents;


use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $table = 'providers';

    protected $fillable = [
        'contr_agent_id',
        'provider_contract_id',
//        'provider_contract',
//        'provider_contract_date',
        'provider_full_name',
        'provider_email',
        'provider_phone',
    ];

    public function document()
    {

    }
}
