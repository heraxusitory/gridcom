<?php


namespace App\Models\References;


use App\Models\Orders\Order;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Model;

class ProviderContractDocument extends Model
{
    protected $table = 'provider_contracts';

    protected $fillable = [
        'uuid',
        'number',
        'date',
        'is_confirmed',
    ];

    public function orders()
    {
        return $this->hasManyThrough(Order::class,
            Provider::class,
            'provider_contract_id',
            'provider_id',
            'id',
            'id'

        );
    }
}
