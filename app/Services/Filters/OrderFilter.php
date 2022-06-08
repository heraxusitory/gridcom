<?php


namespace App\Services\Filters;


use App\Models\Orders\Order;

class OrderFilter extends QueryFilter
{
    public function number(string $number)
    {
        return $this->builder->where('number', 'ILIKE', "%{$number}%");
    }

    public function order_date(string $time_start = null, string $time_end = null)
    {

        if (is_numeric(strtotime($time_start))) {
            $this->builder->whereDate('order_date', '>=', $time_start);
        }
        if (is_numeric(strtotime($time_end))) {
            $this->builder->whereDate('order_date', '<=', $time_end);
        }
        return $this->builder;
    }

    public function deadline_date(string $time_start = null, string $time_end = null)
    {
        if (is_numeric(strtotime($time_start))) {
            $this->builder->whereDate('deadline_date', '>=', $time_start);
        }
        if (is_numeric(strtotime($time_end))) {
            $this->builder->whereDate('deadline_date', '<=', $time_end);
        }
        return $this->builder;

    }

    public function customer_status(string $customer_status)
    {
        return $this->builder->where('customer_status', $customer_status);
    }

    public function provider_status(string $provider_status)
    {
        return $this->builder->where('provider_status', $provider_status);
    }

    public function provider_contract_id(string $provider_contract_id)
    {
        return $this->builder->whereRelation('provider.contract', 'id', $provider_contract_id);
    }

    public function work_agreement_id(string $work_agreement_id)
    {
        return $this->builder->whereRelation('customer.contract', 'id', $work_agreement_id);
    }

    public function organization_id(string $organization_id)
    {
        return $this->builder->whereRelation('customer.organization', 'id', $organization_id);
    }

    public function work_type(string $work_type)
    {
        return $this->builder->whereRelation('customer', 'work_type', $work_type);
    }

    public function object_id(string $object_id)
    {
        return $this->builder->whereRelation('customer.object', 'id', $object_id);
    }

    public function sub_object_id(string $sub_object_id)
    {
        return $this->builder->whereRelation('customer.subObject', 'id', $sub_object_id);
    }

    public function positions_sum_amount_without_vat(float $positions_sum_amount_without_vat)
    {
        $order_ids = Order::query()->withSum('positions', 'amount_without_vat')->get()
            ->filter(function ($order) use ($positions_sum_amount_without_vat) {
                return (float)$order->positions_sum_amount_without_vat === (float)$positions_sum_amount_without_vat;
            })->pluck('id');
        return $this->builder->whereIn('id', $order_ids);
    }
}
