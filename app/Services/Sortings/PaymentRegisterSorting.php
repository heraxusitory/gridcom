<?php


namespace App\Services\Sortings;


class PaymentRegisterSorting extends QuerySorting
{
    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    public function number()
    {
        return $this->builder->orderBy('number', $this->order);
    }

    public function provider_status()
    {
        return $this->builder->orderBy('provider_status', $this->order);
    }

    public function provider()
    {
        return $this->builder
            ->leftJoin('contr_agents', 'payment_registers.provider_contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }

    public function contractor()
    {
        return $this->builder
            ->leftJoin('contr_agents', 'payment_registers.contractor_contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }

    public function provider_contract_name()
    {
        return $this->builder
            ->leftJoin('provider_contracts', 'payment_registers.provider_contract_id', '=', 'provider_contracts.id')
            ->orderBy('provider_contracts.name', $this->order);
    }

    public function provider_contract_date()
    {
        return $this->builder
            ->leftJoin('provider_contracts', 'payment_registers.provider_contract_id', '=', 'provider_contracts.id')
            ->orderBy('provider_contracts.date', $this->order);
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

    public function date()
    {
        return $this->builder->orderBy('date', $this->order);
    }
}
