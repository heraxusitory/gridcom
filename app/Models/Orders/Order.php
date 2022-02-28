<?php

namespace App\Models\Orders;

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\Provider;
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
        'customer_id',
        'provider_id',
        'contractor_id',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(OrderPosition::class, 'order_info_id', 'id');
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
