<?php


namespace App\Services\Filters;


class OrganizationNotificationFilter extends QueryFilter
{
    public function number(string $number)
    {
        return $this->builder->where('id', $number);
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

    public function contract_number(string $contract_number)
    {
        return $this->builder->where('contract_number', $contract_number);
    }

    public function contract_date(string $from = null, string $to = null)
    {

        if (is_numeric(strtotime($from))) {
            $this->builder->whereDate('contract_date', '>=', $from);
        }
        if (is_numeric(strtotime($to))) {
            $this->builder->whereDate('contract_date', '<=', $to);
        }
        return $this->builder;
    }

    public function organization_id(string $organization_id)
    {
        return $this->builder->whereRelation('organization', 'id', $organization_id);
    }

    public function status(string $status)
    {
        return $this->builder->where('status', $status);
    }
}
