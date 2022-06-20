<?php


namespace App\Services\Sortings\ProviderOrders;


use App\Services\Sortings\QuerySorting;

class ActualPositionSorting extends QuerySorting
{
    public function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    protected function number()
    {
        return $this->builder->orderBy('id', $this->order);
    }

    public function nomenclature_name()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'actual_provider_order_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.name', $this->order);
    }

    public function mnemocode()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'actual_provider_order_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.mnemocode', $this->order);
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

    protected function delivery_time()
    {
        return $this->builder->orderBy('delivery_time', $this->order);
    }

    protected function delivery_address()
    {
        return $this->builder->orderBy('delivery_address', $this->order);
    }

    protected function organization_comment()
    {
        return $this->builder->orderBy('organization_comment', $this->order);
    }
}
