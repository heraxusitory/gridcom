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
        /** @var Order $order */
        $order = Order::query()
            ->with([
                'customer.contract',
                'provider.contract',
                'contractor',
                'positions',
                'positions.nomenclature.units',
//                'positions.unit'
            ])
            ->findOrFail($this->order_id);

//        dd($order->provider->contract);
//        $order->map(function ($item) {
//            return $item->provider->contact = [
//                'full_name' => $item->provider->full_name,
//                'email' => $item->provider->email,
//                'phone' => $item->provider->phone,
//            ];
//        });
        $order->amount_total = $order->positions->sum('amount_without_vat');
        return $order;
    }
}
