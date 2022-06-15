<?php


namespace App\Services\Filters;


class ProviderOrderFilter extends QueryFilter
{
    public function number(string $number)
    {
        return $this->builder->where('number', 'ILIKE', "%{$number}%");
    }

    public function order_date(string $order_date = null)
    {
        if (is_numeric(strtotime($order_date))) {
            return $this->builder->whereDate('order_date', $order_date);
        }
        return $this->builder;
    }

    public function contract_stage(string $contract_stage)
    {
        return $this->builder->where('contract_stage', 'ILIKE', "%{$contract_stage}%");
    }

    public function contract_number(string $contract_number)
    {
        return $this->builder->where('contract_number', 'ILIKE', "%{$contract_number}%");
    }

    public function contract_date(string $contract_date = null)
    {
        if (is_numeric(strtotime($contract_date))) {
            return $this->builder->where('contract_date', 'ILIKE', "%{$contract_date}%");
        }
        return $this->builder;
    }

    public function organization_id(string $organization_id)
    {
        return $this->builder->whereRelation('organization', 'id', $organization_id);
    }
}
