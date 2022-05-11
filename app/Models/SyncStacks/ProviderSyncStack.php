<?php

namespace App\Models\SyncStacks;

use App\Models\References\ContrAgent;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderSyncStack extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'provider_sync_stacks';

    protected $fillable = [
        'model',
        'contr_agent_id',
        'entity_id',
    ];

    public function __construct(ContrAgent $contr_agent)
    {
        parent::__construct();
        $this->contr_agent_id = $contr_agent->uuid;
    }
}
