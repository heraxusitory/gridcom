<?php


namespace App\Services\Sortings;


class OrganizationNotificationSorting extends QuerySorting
{
    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    protected function number()
    {
        return $this->builder->orderBy('id', $this->order);
    }

    protected function date()
    {
        return $this->builder->orderBy('date', $this->order);
    }

    protected function status()
    {
        return $this->builder->orderBy('status', $this->order);
    }

    protected function contract_stage()
    {
        return $this->builder->orderBy('contract_stage', $this->order);
    }

    public function organization()
    {
        return $this->builder
            ->leftJoin('organizations', 'organization_notifications.organization_id', '=', 'organizations.id')
            ->orderBy('organizations.name', $this->order);
    }

    public function provider()
    {
        return $this->builder
            ->leftJoin('contr_agents', 'organization_notifications.provider_contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }

    public function contract_number()
    {
        return $this->builder->orderBy('contract_number', $this->order);
    }

    public function contract_date()
    {
        return $this->builder->orderBy('contract_date', $this->order);
    }

    public function date_fact_delivery()
    {
        return $this->builder->orderBy('date_fact_delivery', $this->order);
    }

    public function delivery_address()
    {
        return $this->builder->orderBy('delivery_address', $this->order);
    }

    public function car_info()
    {
        return $this->builder->orderBy('car_info', $this->order);
    }

    public function driver_phone()
    {
        return $this->builder->orderBy('driver_phone', $this->order);
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
