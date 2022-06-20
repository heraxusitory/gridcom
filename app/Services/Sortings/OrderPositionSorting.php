<?php


namespace App\Services\Sortings;


use Illuminate\Support\Facades\DB;

class OrderPositionSorting extends QuerySorting
{
    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    public function number()
    {
        return $this->builder->orderBy('number', $this->order);
    }

    public function status()
    {
        return $this->builder->orderBy('status', $this->order);
    }

    public function nomenclature_name()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'order_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.name', $this->order);
    }

    public function mnemocode()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'order_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.mnemocode', $this->order);
    }

    public function count()
    {
        return $this->builder->orderBy('count', $this->order);
    }

    public function unit()
    {
        return $this->builder
            ->selectRaw("order_positions.*,
             (SELECT nomenclature_units.name FROM nomenclature
                   JOIN nomenclature_to_unit ON nomenclature.id = nomenclature_to_unit.nomenclature_id
                   JOIN nomenclature_units ON nomenclature_to_unit.unit_id = nomenclature_units.id
                   WHERE order_positions.nomenclature_id = nomenclature.id
                   LIMIT 1) as nomenclature_unit_name")
            ->orderBy('nomenclature_unit_name', $this->order);
    }


    public function price_without_vat()
    {
        return $this->builder->orderBy('price_without_vat', $this->order);
    }

    public function amount_without_vat()
    {
        return $this->builder->orderBy('amount_without_vat', $this->order);
    }

    public function delivery_plan_time()
    {
        return $this->builder->orderBy('delivery_plan_time', $this->order);
    }

    public function delivery_time()
    {
        return $this->builder->orderBy('delivery_time', $this->order);
    }

    public function delivery_address()
    {
        return $this->builder->orderBy('delivery_address', $this->order);
    }
}
