<?php


namespace App\Services\Filters\ProviderOrders;


use App\Services\Filters\QueryFilter;

class RequirementCorrectionPositionFilter extends QueryFilter
{
    public function status(string $name)
    {
        return $this->builder->where('status', 'ILIKE', "%{$name}%");
    }

    public function mnemocode(string $mnemocode)
    {
        return $this->builder->whereRelation('nomenclature', 'mnemocode', 'ILIKE', "%{$mnemocode}%");
    }

    public function nomenclature_name(string $name)
    {
        return $this->builder->whereRelation('nomenclature', 'name', 'ILIKE', "%{$name}%");
    }

    public function price_without_vat(string $name)
    {
        return $this->builder->where('price_without_vat', 'ILIKE', "%{$name}%");
    }

    public function delivery_address(string $name)
    {
        return $this->builder->where('delivery_address', 'ILIKE', "%{$name}%");
    }

    public function delivery_time(string $from = null, string $to = null)
    {
        if (is_numeric(strtotime($from))) {
            $this->builder->whereDate('delivery_time', '>=', $from);
        }
        if (is_numeric(strtotime($to))) {
            $this->builder->whereDate('delivery_time', '<=', $to);
        }
        return $this->builder;
    }
}
