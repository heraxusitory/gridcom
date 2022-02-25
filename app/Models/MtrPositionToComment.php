<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MtrPositionToComment extends Model
{
    use HasFactory;
    
    protected $table = ['mtr_positions_to_comments'];

    protected $fillable = [
        'mtr_position_id',
        'comment_id',
    ];
}
