<?php


namespace App\Services\Filters;


class ConsignmentRegisterPositionFilter extends QueryFilter
{
    public function consignment_number(string $number)
    {
        return $this->builder->whereRelation('consignment', 'number', 'ILIKE', "%{$number}%");
    }

    public function consignment_date(string $from = null, string $to = null)
    {
        if (is_numeric(strtotime($from))) {
            $this->builder->whereRelation('consignment', 'date', '>=', $from);
        }
        if (is_numeric(strtotime($to))) {
            $this->builder->whereRelation('consignment', 'date', '<=', $to);
        }
        return $this->builder;
    }

    public function nomenclature_name(string $name)
    {
        return $this->builder->whereRelation('nomenclature', 'name', 'ILIKE', "%{$name}%");
    }

    public function mnemocode(string $name)
    {
        return $this->builder->whereRelation('nomenclature', 'mnemocode', 'ILIKE', "%{$name}%");
    }
}
