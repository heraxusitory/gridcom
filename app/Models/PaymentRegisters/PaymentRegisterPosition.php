<?php


namespace App\Models\PaymentRegisters;


use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PaymentRegisterPosition extends Model
{
    protected $table = 'payment_register_positions';

    protected $fillable = [
        'position_id',
        'payment_register_id',
        'order_id',
        'payment_order_number',
        'payment_order_date',
        'amount_payment',
        'payment_type',
    ];

    protected $casts = [
        'amount_payment' => 'float',
    ];

    private const PAYMENT_TYPE_PREPAID_EXPENSE = 'Аванс';
    private const PAYMENT_TYPE_POSTPAID = 'Постоплата';

    public static function getPaymentTypes()
    {
        return [
            self::PAYMENT_TYPE_PREPAID_EXPENSE,
            self::PAYMENT_TYPE_POSTPAID,
        ];
    }

    public function getPaymentOrderDateAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }


    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function payment_register()
    {
        return $this->belongsTo(PaymentRegister::class, 'payment_register_id', 'id');
    }
}
