<?php

namespace App\Models\Orders;

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\Provider;
use App\Traits\UsesOrderNumber;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory, UsesOrderNumber;

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
        return $this->hasMany(OrderPosition::class, 'order_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
    }
}
