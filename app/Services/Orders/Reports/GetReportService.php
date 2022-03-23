<?php


namespace App\Services\Orders\Reports;


use App\Models\Consignments\Consignment;
use App\Models\Orders\LKK\Order;
use App\Services\IService;

class GetReportService implements IService
{
    private $top_report = null;
    public function __construct(private Order $order)
    {
    }

    public function run()
    {
//        $order = Order::query()
//            ->with([
//                'customer',
//                'provider',
//                'contractor',
//                'positions',
//                'positions.nomenclature',
//                'positions.unit'
//            ])
//            ->findOrFail($this->order_id);

        $this->top_report->amount_total = $this->order->positions->sum('amount_without_vat');

        $order_positions = $this->order->positions()
        $consignments = Consignment::query()
            ->where('order_id', $this->order->id)

    }
}
