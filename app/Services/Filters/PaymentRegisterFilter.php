<?php


namespace App\Services\Filters;


class PaymentRegisterFilter extends QueryFilter
{

    public function number(string $number)
    {
        return $this->builder->where('number', 'ILIKE', "%{$number}%");
    }

    public function provider_status(string $provider_status)
    {
        return $this->builder->where('provider_status', $provider_status);
    }

    public function date(string $from, string $to)
    {
        if (is_numeric(strtotime($from))) {
            $this->builder->whereDate('date', '>=', $from);
        }
        if (is_numeric(strtotime($to))) {
            $this->builder->whereDate('date', '<=', $to);
        }
        return $this->builder;
    }

    public function provider_id(string $provider_id)
    {
        return $this->builder->whereRelation('provider', 'id', $provider_id);
    }

    public function provider_contract_id(string $provider_contract_id)
    {
        return $this->builder->whereRelation('provider_contract', 'id', $provider_contract_id);
    }


    public function provider_contract_date(string $from, string $to)
    {
        if (is_numeric(strtotime($from))) {
            $this->builder->whereRelation('provider_contract', 'date', '>=', $from);
        }
        if (is_numeric(strtotime($to))) {
            $this->builder->whereRelation('provider_contract', 'date', '<=', $to);
        }
        return $this->builder;
    }
}
