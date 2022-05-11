<?php

namespace App\Models\SyncStacks;

use App\Traits\HasSyncStack;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MTOSyncStack extends Model
{
    use HasFactory, HasSyncStack;

    protected $table = 'mto_sync_stacks';

    protected $fillable = [
        'model',
        'entity_id',
    ];
}
