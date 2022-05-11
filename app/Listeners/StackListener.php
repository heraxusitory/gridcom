<?php

namespace App\Listeners;

use App\Events\NewStack;
use App\Models\Orders\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
            $stack->save();
        }
    }
}