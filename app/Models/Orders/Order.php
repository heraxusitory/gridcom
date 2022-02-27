<?php

namespace App\Models\Orders;

use App\Models\ContrAgents\Contractor;
use App\Models\ContrAgents\Customer;
use App\Models\ContrAgents\Provider;
use App\Models\MtrPositions\MtrPosition;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'orders';

    protected $fillable = [
        'number',
        'order_date',
        'deadline_date',
        'customer_status',
        'provider_status',
        //Todo вынести в отедльную таблицу
        'customer_id',
//        'customer_filial_branch',
//        'work_agreement',
//        'work_agreement_date',
//        'work_type',
//        'object',
//        'sub_object',
        //Todo вынести в отедльную таблицу
        'provider_id',
//        'provider',
//        'provider_contract',
//        'provider_contract_date',
//        'provider_full_name',
//        'provider_email',
//        'provider_phone',
        //Todo вынести в отедльную таблицу
        'contractor_id',
//        'contractor',
//        'contractor_full_name',
//        'contractor_email',
//        'contractor_phone',
//        'contractor_responsible_full_name',
//        'contractor_responsible_phone',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(MtrPosition::class, 'order_info_id', 'id');
    }

    public function customer(): hasOne
    {
        return $this->hasOne(Customer::class, 'customer_id', 'id');
    }

    public function provider(): hasOne
    {
        return $this->hasOne(Provider::class, 'provider_id', 'id');
    }

    public function contractor(): hasOne
    {
        return $this->hasOne(Contractor::class, 'contractor_id', 'id');
    }
}
