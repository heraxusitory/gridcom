<?php

namespace App\Observers;

use App\Models\Orders\Order;

class OrderObserver
{
    public function creating(Order $order)
    {
        $order->sync_required = true;
    }
}
