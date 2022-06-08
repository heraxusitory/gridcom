<?php


namespace App\Services\Sortings;


class ConsignmentSorting extends QuerySorting
{

    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    public function number()
    {
        return $this->builder->orderBy('number', $this->order);
    }

    public function date()
    {
        return $this->builder->orderBy('date', $this->order);
    }

    public function comment()
    {
        return $this->builder->orderBy('comment', $this->order);
    }

    public function responsible_full_name()
    {
        return $this->builder->orderBy('responsible_full_name', $this->order);
    }

    public function responsible_phone()
    {
        return $this->builder->orderBy('responsible_phone', $this->order);
    }

    public function organization()
    {
        return $this->builder
            ->leftJoin('organizations', 'consignments.organization_id', '=', 'organizations.id')
            ->orderBy('organizations.name', $this->order);
    }

    public function provider()
    {
        return $this->builder
            ->leftJoin('contr_agents', 'consignments.provider_contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }

    public function contractor()
    {
        return $this->builder
            ->leftJoin('contr_agents', 'consignments.contractor_contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }

    public function provider_contract_name()
    {
        return $this->builder
            ->leftJoin('provider_contracts', 'consignments.provider_contract_id', '=', 'provider_contracts.id')
            ->orderBy('provider_contracts.name', $this->order);
    }

    public function provider_contract_date()
    {
        return $this->builder
            ->leftJoin('provider_contracts', 'consignments.provider_contract_id', '=', 'provider_contracts.id')
            ->orderBy('provider_contracts.date', $this->order);
    }

    public function work_agreement_name()
    {
        return $this->builder
            ->leftJoin('work_agreements', 'consignments.work_agreement_id', '=', 'work_agreements.id')
            ->orderBy('work_agreements.name', $this->order);
    }

    public function work_agreement_date()
    {
        return $this->builder
            ->leftJoin('work_agreements', 'consignments.work_agreement_id', '=', 'work_agreements.id')
            ->orderBy('work_agreements.date', $this->order);
    }

    public function object()
    {
        return $this->builder
            ->leftJoin('customer_objects', 'consignments.customer_object_id', '=', 'customer_objects.id')
            ->orderBy('customer_objects.name', $this->order);
    }

    public function sub_object()
    {
        return $this->builder
            ->leftJoin('customer_sub_objects', 'consignments.customer_sub_object_id', '=', 'customer_sub_objects.id')
            ->orderBy('customer_sub_objects.name', $this->order);
    }
}
