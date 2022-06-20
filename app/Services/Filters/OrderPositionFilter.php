<?php


namespace App\Services\Filters;


class OrderPositionFilter extends QueryFilter
{
    public function status(string $status)
    {
        return $this->builder->where('status', 'ILIKE', "%{$status}%");
    }

    public function mnemocode(string $mnemocode)
    {
        return $this->builder->whereRelation('nomenclature', 'mnemocode', $mnemocode);
    }

    public function nomenclature_name(string $nomenclature_name)
    {
        return $this->builder->whereRelation('nomenclature', 'name', $nomenclature_name);
    }

    public function price_without_vat(string $price_without_vat)
    {
        return $this->builder->where('price_without_vat', 'ILIKE', "%{$price_without_vat}%");
    }

    public function delivery_time(string $delivery_time)
    {
        return $this->builder->where('delivery_time', 'ILIKE', "%{$delivery_time}%");
    }

    public function delivery_address(string $delivery_address)
    {
        return $this->builder->where('delivery_address', 'ILIKE', "%{$delivery_address}%");
    }
}
