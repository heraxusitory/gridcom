<?php


namespace App\Models\PaymentRegisters;


use Illuminate\Database\Eloquent\Model;

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

    private const PAYMENT_TYPE_PREPAID_EXPENSE = 'Аванс';
    private const PAYMENT_TYPE_POSTPAID = 'Постоплата';

    public static function getPaymentTypes()
    {
        return [
            self::PAYMENT_TYPE_PREPAID_EXPENSE,
            self::PAYMENT_TYPE_POSTPAID,
        ];
    }
}
