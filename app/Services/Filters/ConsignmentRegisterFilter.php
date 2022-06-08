<?php


namespace App\Services\Filters;


class ConsignmentRegisterFilter extends QueryFilter
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

    public function work_agreement_id(string $work_agreement_id)
    {
        return $this->builder->whereRelation('work_agreement', 'id', $work_agreement_id);
    }

    public function customer_status(string $customer_status)
    {
        return $this->builder->where('customer_status', $customer_status);
    }
}
