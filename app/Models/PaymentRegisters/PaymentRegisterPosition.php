<?php


namespace App\Models\PaymentRegisters;


use Illuminate\Database\Eloquent\Model;

class PaymentRegisterPosition extends Model
{
    protected $table = 'payment_register_positions';

    protected $fillable = [
        'uuid',
        'payment_register_id',
        'order_id',
        'payment_order',
        'payment_order_date',
        'amount_payment',
        'payment_type',
    ];
}
