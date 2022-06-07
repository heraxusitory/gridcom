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
        return $this->builder->with(['customer' => function ($q) {
            $q->orderBy('work_type', $this->order);
        }]);
    }

    public function organization()
    {
        return $this->builder->with(['customer.organization' => function ($q) {
            $q->orderBy('name', $this->order);
        }]);
    }

    public function positions_sum_amount_without_vat()
    {
//        return $this->builder->with(['customer.organization' => function ($q) {
//            $q->orderBy('name', $this->order);
//        }]);
        return $this->builder->withSum('positions', 'amount_without_vat')->orderBy('positions_sum_amount_without_vat', $this->order);
    }
}
