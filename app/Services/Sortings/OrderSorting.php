<?php


namespace App\Services\Sortings;


use App\Models\Orders\Order;

class OrderSorting extends QuerySorting
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

    public function deadline_date()
    {
        return $this->builder->orderBy('deadline_date', $this->order);
    }

    public function customer_status()
    {
        return $this->builder->orderBy('customer_status', $this->order);
    }

    public function provider_status()
    {
        return $this->builder->orderBy('provider_status', $this->order);
    }

    public function work_type()
    {
        return $this->builder
            ->leftJoin('order_customers', 'orders.customer_id', '=', 'order_customers.id')
            ->orderBy('order_customers.work_type', $this->order);
    }

    public function organization()
    {
        return $this->builder
            ->leftJoin('order_customers', 'orders.customer_id', '=', 'order_customers.id')
            ->leftJoin('organizations', 'order_customers.organization_id', '=', 'organizations.id')
            ->orderBy('organizations.name', $this->order);
    }

    public function work_start_date()
    {
        return $this->builder
            ->leftJoin('order_customers', 'orders.customer_id', '=', 'order_customers.id')
            ->orderBy('order_customers.work_start_date', $this->order);
    }

    public function work_end_date()
    {
        return $this->builder
            ->leftJoin('order_customers', 'orders.customer_id', '=', 'order_customers.id')
            ->orderBy('order_customers.work_end_date', $this->order);
    }

    public function responsible_full_name()
    {
        return $this->builder
            ->leftJoin('order_contractors', 'orders.contractor_id', '=', 'order_contractors.id')
            ->orderBy('order_contractors.contractor_responsible_full_name', $this->order);
    }

    public function responsible_phone()
    {
        return $this->builder
            ->leftJoin('order_contractors', 'orders.contractor_id', '=', 'order_contractors.id')
            ->orderBy('order_contractors.contractor_responsible_phone', $this->order);
    }

    public function comment()
    {
        return $this->builder
            ->leftJoin('order_contractors', 'orders.contractor_id', '=', 'order_contractors.id')
            ->orderBy('order_contractors.comment', $this->order);
    }

    public function positions_sum_amount_without_vat()
    {
        return $this->builder->withSum('positions', 'amount_without_vat')->orderBy('positions_sum_amount_without_vat', $this->order);
    }

    public function work_agreement_number()
    {
        return $this->builder
//            ->selectRaw('work_agreements.number as work_agreement_number')
            ->leftJoin('order_customers', 'orders.customer_id', '=', 'order_customers.id')
            ->leftJoin('work_agreements', 'order_customers.work_agreement_id', '=', 'work_agreements.id')
            ->orderBy('work_agreements.number', $this->order);
    }

    public function work_agreement_date()
    {
        return $this->builder
//            ->selectRaw('work_agreements.date as work_agreement_date')
            ->leftJoin('order_customers', 'orders.customer_id', '=', 'order_customers.id')
            ->leftJoin('work_agreements', 'order_customers.work_agreement_id', '=', 'work_agreements.id')
            ->orderBy('work_agreements.date', $this->order);
    }

    public function provider_contract_number()
    {
        return $this->builder
//            ->selectRaw('work_agreements.number as work_agreement_number')
            ->leftJoin('order_providers', 'orders.provider_id', '=', 'order_providers.id')
            ->leftJoin('provider_contracts', 'order_providers.provider_contract_id', '=', 'provider_contracts.id')
            ->orderBy('provider_contracts.number', $this->order);
    }

    public function provider_contract_date()
    {
        return $this->builder
//            ->selectRaw('work_agreements.date as work_agreement_date')
            ->leftJoin('order_providers', 'orders.provider_id', '=', 'order_providers.id')
            ->leftJoin('provider_contracts', 'order_providers.provider_contract_id', '=', 'provider_contracts.id')
            ->orderBy('provider_contracts.date', $this->order);
    }

    public function object()
    {
        return $this->builder
            ->leftJoin('order_customers', 'orders.customer_id', '=', 'order_customers.id')
            ->leftJoin('customer_objects', 'order_customers.object_id', '=', 'customer_objects.id')
            ->orderBy('customer_objects.name', $this->order);
    }

    public function sub_object()
    {
        return $this->builder
            ->leftJoin('order_customers', 'orders.customer_id', '=', 'order_customers.id')
            ->leftJoin('customer_sub_objects', 'order_customers.sub_object_id', '=', 'customer_sub_objects.id')
            ->orderBy('customer_sub_objects.name', $this->order);
    }

    public function provider()
    {
        return $this->builder
            ->leftJoin('order_providers', 'orders.provider_id', '=', 'order_providers.id')
            ->leftJoin('contr_agents', 'order_providers.contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }

    public function contractor()
    {
        return $this->builder
            ->leftJoin('order_contractors', 'orders.contractor_id', '=', 'order_contractors.id')
            ->leftJoin('contr_agents', 'order_contractors.contr_agent_id', '=', 'contr_agents.id')
            ->orderBy('contr_agents.name', $this->order);
    }
}
