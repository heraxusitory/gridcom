<?php

namespace App\Events;

use App\Interfaces\Syncable;
use App\Interfaces\SyncStackable;
use App\Models\Orders\Order;
use App\Models\References\ContrAgent;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewStack
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var SyncStackable
     */
    private SyncStackable $user;
    /**
     * @var Order
     */
    public Order $order;
    /**
     * @var SyncStackable
     */
    public SyncStackable $stack;

    /**
     * Create a new event instance.
     *
     * @param Syncable $model_object
     * @param SyncStackable $stack
     */
    public function __construct(Syncable $model_object, SyncStackable $stack)
    {
        $this->model_object = $model_object;
        $this->stack = $stack;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
