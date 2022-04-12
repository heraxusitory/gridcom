<?php


namespace App\Services\Orders;


use App\Models\Orders\Order;
use App\Services\IService;

class GetOrderService implements IService
{
    private $payload;
    private $order_id;

    public function __construct($payload, $order_id)
    {
        $this->payload = $payload;
        $this->order_id = $order_id;
    }

    public function run()
    {
        $order = Order::query()
            ->with([
                'customer',
                'provider',
                'contractor',
                'positions',
                'positions.nomenclature',
                'positions.unit'
            ])
            ->findOrFail($this->order_id);

        $order->amount_total = $order->positions->sum('amount_without_vat');
        return $order;
    }
}
