<?php


namespace App\Models\Consignments;


use App\Models\Orders\LKK\Order;
use App\Traits\UsesConsignmentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consignment extends Model
{
    use HasFactory, UsesConsignmentNumber;

    protected $table = 'consignments';

    protected $fillable = [
        'uuid',
        'number',
        'date',
        'order_id',
        'responsible_full_name',
        'responsible_phone',
        'comment',
    ];

    public function order(): hasOne
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function positions()
    {
        return $this->hasMany(ConsignmentPosition::class, 'consignment_id', 'id');
    }
}