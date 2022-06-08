<?php


namespace App\Services\Sortings;


class ConsignmentRegisterSorting extends QuerySorting
{

    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    public function date()
    {
        return $this->builder->orderBy('date', $this->order);
    }

    public function customer_status()
    {
        return $this->builder->orderBy('customer_status', $this->order);
    }

    public function contr_agent_status()
    {
        return $this->builder->orderBy('contr_agent_status', $this->order);
    }

    public function organization()
    {
        return $this->builder
            ->leftJoin('organizations', 'consignment_registers.organization_id', '=', 'organizations.id')
            ->orderBy('organizations.name', $this->order);
    }

    public function contractor()
    {
        return $this->builder
            ->leftJoin('contr_agents', 'consignment_registers.contractor_contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }

    public function provider()
    {
        return $this->builder
            ->leftJoin('contr_agents', 'consignment_registers.provider_contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }

    public function object()
    {
        return $this->builder
            ->leftJoin('customer_objects', 'consignment_registers.customer_object_id', '=', 'customer_objects.id')
            ->orderBy('customer_objects.name', $this->order);
    }

    public function sub_object()
    {
        return $this->builder
            ->leftJoin('customer_sub_objects', 'consignment_registers.customer_sub_object_id', '=', 'customer_sub_objects.id')
            ->orderBy('customer_sub_objects.name', $this->order);
    }

    public function responsible_full_name()
    {
        return $this->builder->orderBy('responsible_full_name', $this->order);
    }

    public function responsible_phone()
    {
        return $this->builder->orderBy('responsible_phone', $this->order);
    }

    public function comment()
    {
        return $this->builder->orderBy('comment', $this->order);
    }
}
