<?php


namespace App\Services\Sortings;


class ProviderOrderSorting extends QuerySorting
{
    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    public function number()
    {
        return $this->builder->orderBy('number', $this->order);
    }

    public function order_date()
    {
        return $this->builder->orderBy('order_date', $this->order);
    }

    public function contract_number()
    {
        return $this->builder->orderBy('contract_number', $this->order);
    }

    public function contract_date()
    {
        return $this->builder->orderBy('contract_date', $this->order);
    }

    public function contract_stage()
    {
        return $this->builder->orderBy('contract_stage', $this->order);
    }

    public function provider()
    {
        return $this->builder
            ->leftJoin('contr_agents', 'provider_orders.provider_contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }

    public function organization()
    {
        return $this->builder
            ->leftJoin('organizations', 'provider_orders.organization_id', '=', 'organizations.id')
            ->orderBy('organizations.name', $this->order);
    }

    public function responsible_full_name()
    {
        return $this->builder->orderBy('responsible_full_name', $this->order);
    }

    public function responsible_phone()
    {
        return $this->builder->orderBy('responsible_phone', $this->order);
    }

    public function organization_comment()
    {
        return $this->builder->orderBy('organization_comment', $this->order);
    }
}
