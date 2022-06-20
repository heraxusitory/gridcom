<?php


namespace App\Services\Sortings;


class ConsignmentPositionSorting extends QuerySorting
{

    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    protected function number()
    {
        return $this->builder->orderBy('id', $this->order);
    }

    public function order_number()
    {
        return $this->builder
            ->leftJoin('orders', 'consignment_positions.order_id', '=', 'orders.id')
            ->orderBy('orders.number', $this->order);
    }

    public function order_date()
    {
        return $this->builder
            ->leftJoin('orders', 'consignment_positions.order_id', '=', 'orders.id')
            ->orderBy('orders.order_date', $this->order);
    }

    public function mnemocode()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'consignment_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.mnemocode', $this->order);
    }

    public function nomenclature_name()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'consignment_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.name', $this->order);
    }

    protected function count()
    {
        return $this->builder->orderBy('count', $this->order);
    }

    protected function price_without_vat()
    {
        return $this->builder->orderBy('price_without_vat', $this->order);
    }

    protected function amount_without_vat()
    {
        return $this->builder->orderBy('amount_without_vat', $this->order);
    }

    protected function vat_rate()
    {
        return $this->builder->orderBy('vat_rate', $this->order);
    }

    protected function amount_with_vat()
    {
        return $this->builder->orderBy('amount_with_vat', $this->order);
    }

    protected function country()
    {
        return $this->builder->orderBy('country', $this->order);
    }

    protected function cargo_custom_declaration()
    {
        return $this->builder->orderBy('cargo_custom_declaration', $this->order);
    }

    protected function declaration()
    {
        return $this->builder->orderBy('declaration', $this->order);
    }
}
