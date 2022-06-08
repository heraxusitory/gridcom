<?php


namespace App\Services\Filters;


class ConsignmentFilter extends QueryFilter
{
    public function number(string $number)
    {
        return $this->builder->where('number', 'ILIKE', "%{$number}%");
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

    public function organization_id(string $organization_id)
    {
        return $this->builder->whereRelation('organization', 'id', $organization_id);
    }

    public function provider_id(string $provider_id)
    {
        return $this->builder->whereRelation('provider', 'id', $provider_id);
    }

    public function contractor_id(string $contractor_id)
    {
        return $this->builder->whereRelation('contractor', 'id', $contractor_id);
    }

    public function work_agreement_id(string $work_agreement_id)
    {
        return $this->builder->whereRelation('work_agreement_id', 'id', $work_agreement_id);
    }

    public function work_agreement_date(string $from, string $to)
    {
        if (is_numeric(strtotime($from))) {
            $this->builder->whereRelation('work_agreement', 'date', '>=', $from);
        }
        if (is_numeric(strtotime($to))) {
            $this->builder->whereRelation('work_agreement', 'date', '<=', $to);
        }
        return $this->builder;
    }

    public function object_id(string $object_id)
    {
        return $this->builder->whereRelation('object', 'id', $object_id);
    }

    public function sub_object_id(string $object_id)
    {
        return $this->builder->whereRelation('subObject', 'id', $object_id);
    }
}
