<?php


namespace App\Services\Orders\Reports;


use App\Models\Consignments\Consignment;
use App\Models\Consignments\ConsignmentPosition;
use App\Models\Orders\Order;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\PaymentRegisters\PaymentRegisterPosition;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;

class GetReportService implements IService
{
    private $top_report = [];
    private $bottom_report = [];

    public function __construct(private Request $request, private Order $order)
    {
    }

    public function run()
    {

        //Сумма заказа
        $this->top_report['amount_total'] = $this->order->positions->sum('amount_without_vat');


        $consignment_last_date = Consignment::query()
            ->whereHas('positions', function ($q) {
                $q->where('order_id', $this->order->id);
            })
            ->orderByDesc('date')
            ->first()?->date;
        //Срок оплаты
        $this->top_report['payment_period'] = isset($consignment_last_date) ? (new Carbon($consignment_last_date))->format('d.m.Y') . ' - ' . (new Carbon($consignment_last_date))->addMonth()->format('d.m.Y') : null;
        //Плановый срок исполнения по заказу
        $this->top_report['plan_deadline_date'] = $this->order->deadline_date;
        //Фактический срок исполнения по заказу
        $this->top_report['fact_deadline_date'] = $consignment_last_date;

        $payment_positions = PaymentRegisterPosition::query()
            ->whereRelation('payment_register', 'provider_status', PaymentRegister::PROVIDER_STATUS_AGREED)
            ->selectRaw('payment_registers.date as payment_register_date, payment_register_positions.amount_payment as payment_register_amount_payment')
            ->where('order_id', $this->order->id)
            ->leftJoin('payment_registers', 'payment_register_positions.payment_register_id', '=', 'payment_registers.id')
            ->orderBy('payment_registers.date')
            ->get();

        $payment_fact = $payment_positions->sum('payment_register_amount_payment');

        //Факт оплаты
        $this->top_report['payment_fact'] = $payment_fact;
        //Факт оплаты(подробный отчет)
        $this->top_report['payment_fact_data'] = $payment_positions;

        $consignment_positions_for_top_report = ConsignmentPosition::query()
            ->whereRelation('consignment', 'is_approved', true)
            ->selectRaw("
            consignment_positions.amount_with_vat as consignment_position_amount_with_vat,
            consignments.date as consignment_date
            ")
            ->join('consignments', 'consignment_positions.consignment_id', 'consignments.id')
            ->where('consignment_positions.order_id', $this->order->id)
            ->orderBy('consignments.date')
            ->get();

        //Факт отгрузки
        $this->top_report['shipment_fact'] = $consignment_positions_for_top_report->sum('consignment_position_amount_with_vat');
        //Факт отгрузки(подробный отчет)
        $this->top_report['shipment_fact_data'] = $consignment_positions_for_top_report;
        //Сальдо
        $this->top_report['balance'] = abs($this->top_report['payment_fact'] - $this->top_report['shipment_fact']);

        $order_positions = $this->order->positions()->with(['nomenclature.units'])
            ->selectRaw("
                nomenclature.id as nomenclature_id,
                nomenclature.mnemocode as nomenclature_mnemocode,
                nomenclature_units.name as nomenclature_unit,
                order_positions.delivery_time,
                order_positions.count as delivery_plan_count,
                order_positions.price_without_vat,
                order_positions.amount_without_vat,
                order_positions.delivery_plan_time
                ")
            ->leftJoin('nomenclature', 'order_positions.nomenclature_id', '=', 'nomenclature.id')
            ->leftJoin('nomenclature_to_unit', 'nomenclature_to_unit.nomenclature_id', '=', 'nomenclature.id')
            ->leftJoin('nomenclature_units', 'nomenclature_to_unit.unit_id', '=', 'nomenclature_units.id')
            ->orderBy('order_positions.delivery_time')
            ->get();


        /************для нижнего отчета в подробном виде*****************/
        $consignment_positions = ConsignmentPosition::query()
            ->whereRelation('consignment', 'is_approved', true)
            ->selectRaw("
            nomenclature.mnemocode as nomenclature_mnemocode,
            SUM(consignment_positions.count) as delivery_fact_count,
            consignments.date as delivery_fact_time
            ")
            ->join('consignments', 'consignment_positions.consignment_id', 'consignments.id')
            ->where('consignment_positions.order_id', $this->order->id)
            ->join('nomenclature', 'consignment_positions.nomenclature_id', '=', 'nomenclature.id')
            ->groupBy(['nomenclature.mnemocode', 'consignments.date'])
            ->orderBy('consignments.date')
            ->get();


        /*order_positions->map(function ($order_position) use ($consignment_positions)*/
//        $pre = $order_positions->toArray();
//        $pre_consign = $consignment_positions->toArray();
        for ($i = 0; $i < $order_positions->count(); $i++) {
            $order_position = $order_positions[$i];

            foreach ($consignment_positions as $key => &$consignment_position) {
                if ($consignment_position['nomenclature_mnemocode'] === $order_position['nomenclature_mnemocode']) {
                    if ($i === $order_positions->count() - 1) {
                        $order_position->delivery_fact_count = $consignment_position['delivery_fact_count'];
                        $order_position->delivery_fact_time = $consignment_position['delivery_fact_time'];
                        unset($consignment_positions[$key]);
                        $order_positions[$i] = $order_position;
                        break;
                    }
                    if ($consignment_position['delivery_fact_count'] > $order_position['delivery_plan_count']) {
                        $consignment_position['delivery_fact_count'] =
                            abs($consignment_position['delivery_fact_count'] - $order_position['delivery_plan_count']);
                        $order_position->delivery_fact_count = $order_position['delivery_plan_count'];
                        $order_position->delivery_fact_time = $consignment_position['delivery_fact_time'];
                        $order_positions[$i] = $order_position;
                        break;
                    } else {
                        $order_position->delivery_fact_count = $consignment_position['delivery_fact_count'];
                        $order_position->delivery_fact_time = $consignment_position['delivery_fact_time'];
                        unset($consignment_positions[$key]);
                        $order_positions[$i] = $order_position;
                        break;
                    }
                }
            }
            if (!isset($order_position->delivery_fact_count)) {
                $order_position->delivery_fact_count = 0;
                $order_position->delivery_fact_time = null;
                $order_positions[$i] = $order_position;
            }
        }

        $order_positions->map(function ($position) {
            $position->remainder = abs($position->delivery_plan_count - $position->delivery_fact_count);
            $position->fact_amount_without_vat = round($position->delivery_fact_count * $position->price_without_vat, 2);
            unset($position->price_without_vat,
                $position->nomenclature_id,
                $position->nomenclature_mnemocode,
                $position->nomenclature_unit);
            return $position;
        });

        if ($this->request?->mnemocode) {
            $order_positions = $order_positions->filter(function ($position) {
                return Str::contains(Str::lower($position->nomenclature->mnemocode), Str::lower($this->request->mnemocode));
            });
        }
        if ($this->request?->nomenclature_name) {
            $order_positions = $order_positions->filter(function ($position) {
                return Str::contains(Str::lower($position->nomenclature->name), Str::lower($this->request->nomenclature_name));
            });
        }

        if ($this->request?->sort_field) {
            $sort_order_value = Str::camel(data_get($this->request, 'sort_order'));
            $sort_order = $sort_order_value === 'asc' ? 'sortBy' : 'sortByDesc';
            $sort_field = trim($this->request->sort_field);
            $order_positions = match ($sort_field) {
                'nomenclature_name' => $order_positions->$sort_order('nomenclature.name')->values()->all(),
                'mnemocode' => $order_positions->$sort_order('nomenclature.mnemocode')->values()->all(),
                'unit' => $order_positions->$sort_order('nomenclature.units.0')->values()->all(),
                'delivery_plan_count' => $order_positions->$sort_order('delivery_plan_count')->values()->all(),
                'delivery_fact_count' => $order_positions->$sort_order('delivery_fact_count')->values()->all(),
                'delivery_time' => $order_positions->$sort_order('delivery_time')->values()->all(),
                'delivery_fact_time' => $order_positions->$sort_order('delivery_fact_time')->values()->all(),
                'remainder' => $order_positions->$sort_order('remainder')->values()->all(),
                'delivery_plan_time' => $order_positions->$sort_order('delivery_plan_time')->values()->all(),
                'fact_amount_without_vat' => $order_positions->$sort_order('fact_amount_without_vat')->values()->all(),
                'amount_without_vat' => $order_positions->$sort_order('amount_without_vat')->values()->all(),
                default => $order_positions,
            };
        }

        $this->bottom_report = new Paginator($order_positions, $this->request->per_page);

        return [
            'top_report' => $this->top_report,
            'bottom_report' => $this->bottom_report,
        ];
    }
}
