<?php

namespace App\Models\MtrPositions;

use App\Models\Comments\Comment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MtrPosition extends Model
{
    use HasFactory;

    protected $table = 'mtr_positions'; #positons in order

    protected $fillable = [
        'order_info_id',
//        'mnemocode',
//        'nomenclature',
//        'unit',    Id позиции
        'count',
        'price_without_vat',
        'amount_without_vat',
        'total_amount',
        'delivery_time',
        'delivery_address',
    ];

    public function comments(): BelongsToMany
    {
        return $this->belongsToMany(Comment::class, 'mtr_positions_to_comments', 'mtr_position_id', 'comment_id');
    }
}
