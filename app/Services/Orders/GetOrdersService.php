<?php


namespace App\Services\Orders;


use App\Models\Orders\Order;
use App\Services\IService;

class GetOrdersService implements IService
{
    private $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function run()
    {
        $orders = Order::query()->with(['customer', 'provider', 'contractor'])->get();
        return $orders;
    }
}
