<?php

namespace App\Models\Orders\OrderPositions;

use App\Models\Comments\Comment;
use App\Models\References\Nomenclature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderPosition extends Model
{
    use HasFactory;

    protected $table = 'order_positions'; #positons in order

    protected $fillable = [
        'order_id',
        'status',
        'nomenclature_id',
        'unit_id',
        'count',
        'price_without_vat',
        'amount_without_vat',
//        'total_amount',
        'delivery_time',
        'delivery_address',
    ];

    const STATUS_AGREED = 'Согласовано';
    const STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const STATUS_REJECTED = 'Отклонено';

    public function nomenclature()
    {
        return $this->belongsTo(Nomenclature::class, 'nomenclature_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Nomenclature::class, 'unit_id', 'id');
    }

//    public function comments(): BelongsToMany
//    {
//        return $this->belongsToMany(Comment::class, 'mtr_positions_to_comments', 'mtr_position_id', 'comment_id');
//    }
}
