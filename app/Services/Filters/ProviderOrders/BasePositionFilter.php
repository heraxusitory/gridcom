<?php


namespace App\Services\Filters\ProviderOrders;


use App\Services\Filters\QueryFilter;

class BasePositionFilter extends QueryFilter
{
    public function nomenclature_name(string $name)
    {
        return $this->builder->whereRelation('nomenclature', 'name', 'ILIKE', "%{$name}%");
    }

    public function mnemocode(string $name)
    {
        return $this->builder->whereRelation('nomenclature', 'mnemocode', 'ILIKE', "%{$name}%");
    }
}
