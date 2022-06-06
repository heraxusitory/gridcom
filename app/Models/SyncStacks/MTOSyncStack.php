<?php

namespace App\Models\SyncStacks;

use App\Interfaces\Syncable;
use App\Interfaces\SyncStackable;
use App\Traits\HasSyncStack;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class MTOSyncStack extends Model implements SyncStackable
{
    use HasFactory, HasSyncStack, UsesUuid;

    /**
     * @var mixed|string
     */
    private static $model_class;
    protected $table = 'mto_sync_stacks';

    protected $fillable = [
        'model',
        'entity_id',
    ];

    public static function getModelEntities(string $model_class)
    {
        self::$model_class = $model_class;
        return self::query()
            ->where('model', $model_class)
            ->with('entity')
            ->get()
            ->filter(function ($stack) {
                return isset($stack->entity);
            })
            ->map(function ($stack) {
                $stack->entity->stack_id = $stack->id;
                return $stack->entity;

            });
    }
}
