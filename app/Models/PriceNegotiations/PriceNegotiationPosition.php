<?php


namespace App\Models\PriceNegotiations;


use Illuminate\Database\Eloquent\Model;

class PriceNegotiationPosition extends Model
{
    protected $table = 'price_negotiation_positions';

    protected $fillable = [
        'position_id',
        'price_negotiation_id',
        'nomenclature_id',
        'new_price_without_vat',
        'agreed_price',
    ];
}
