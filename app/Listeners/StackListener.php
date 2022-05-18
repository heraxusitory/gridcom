<?php

namespace App\Listeners;

use App\Events\NewStack;
use App\Models\IntegrationUser;
use App\Models\Orders\Order;
use App\Models\SyncStacks\MTOSyncStack;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StackListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param NewStack $event
     * @return void
     */
    public function handle(NewStack $event)
    {
        $model_object = $event->model_object;
        $stacks = $event->stacks;
        foreach ($stacks as $stack) {
            $stack->model = $model_object::class;
            $stack->entity_id = $model_object->id;
            if (!($stack instanceof MTOSyncStack) && !Str::isUuid($stack->contr_agent_id) &&
                $user = IntegrationUser::query()
                    ->whereRelation('contr_agent', 'uuid', $stack->contr_agent_id)
                    ->doesntExist()) {
                continue;
            }
            $stack->save();
        }
    }
}
