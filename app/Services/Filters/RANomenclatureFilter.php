<?php


namespace App\Services\Filters;


class RANomenclatureFilter extends QueryFilter
{
    public function number(string $number)
    {
        return $this->builder->where('number', 'ILIKE', "%{$number}%");
    }

    public function date(string $from = null, string $to = null)
    {
        if (is_numeric(strtotime($from))) {
            $this->builder->whereDate('date', '>=', $from);
        }
        if (is_numeric(strtotime($to))) {
            $this->builder->whereDate('date', '<=', $to);
        }
        return $this->builder;
    }

    public function organization_status(string $organization_status)
    {
        return $this->builder->where('organization_status', $organization_status);
    }
}
