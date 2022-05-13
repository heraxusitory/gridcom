<?php

namespace App\Models\SyncStacks;

use App\Interfaces\SyncStackable;
use App\Models\References\ContrAgent;
use App\Traits\HasSyncStack;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorSyncStack extends Model implements SyncStackable
{
    use HasFactory, HasSyncStack, UsesUuid;

    /**
     * @var mixed|string
     */
    private static $model_class;
    protected $table = 'contractor_sync_stacks';

    protected $fillable = [
        'model',
        'contr_agent_id',
        'entity_id',
    ];

    public function __construct(?ContrAgent $contr_agent = null)
    {
        parent::__construct();
        $this->contr_agent_id = $contr_agent?->uuid;
    }

    public static function getModelEntities(string $model_class, ContrAgent $contr_agent)
    {
        self::$model_class = $model_class;
        return self::query()
            ->where('contr_agent_id', $contr_agent->uuid)
            ->where('model', $model_class)
            ->with('entity')
            ->get()
            ->map(function ($stack) {
                $stack->entity->stack_id = $stack->id;
                return $stack->entity;
            });
    }
}
