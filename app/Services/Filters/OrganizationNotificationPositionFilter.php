<?php


namespace App\Services\Filters;


class OrganizationNotificationPositionFilter extends QueryFilter
{
    public function order_number(string $number)
    {
        return $this->builder->whereRelation('order', 'number', 'ILIKE', "%{$number}%");
    }

    public function mnemocode(string $mnemocode)
    {
        return $this->builder->whereRelation('nomenclature', 'mnemocode', 'ILIKE', "%{$mnemocode}%");
    }

    public function nomenclature_name(string $nomenclature_name)
    {
        return $this->builder->whereRelation('nomenclature', 'name', 'ILIKE', "%{$nomenclature_name}%");
    }
}
