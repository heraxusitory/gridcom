<?php

namespace App\Models\Comments;

use App\Models\MtrPositions\MtrPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $fillable = [
        'user_id',
        'text',
    ];

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(MtrPosition::class, 'mtr_positions_to_comments', 'comment_id', 'mtr_position_id');

    }
}
