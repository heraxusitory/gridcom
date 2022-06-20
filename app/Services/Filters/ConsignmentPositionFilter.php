<?php


namespace App\Services\Filters;


class ConsignmentPositionFilter extends QueryFilter
{
    public function order_number(string $number)
    {
        return $this->builder->whereRelation('order', 'number', 'ILIKE', "%{$number}%");
    }

    public function mnemocode(string $mnemocode)
    {
        return $this->builder->whereRelation('nomenclature', 'mnemocode', 'ILIKE', "%{$mnemocode}%");
    }

    public function nomenclature_name(string $name)
    {
        return $this->builder->whereRelation('nomenclature', 'name', 'ILIKE', "%{$name}%");
    }

    public function country(string $name)
    {
        return $this->builder->where('country', 'ILIKE', "%{$name}%");
    }

    public function cargo_custom_declaration(string $name)
    {
        return $this->builder->where('cargo_custom_declaration', 'ILIKE', "%{$name}%");
    }

    public function declaration(string $name)
    {
        return $this->builder->where('declaration', 'ILIKE', "%{$name}%");
    }
}
