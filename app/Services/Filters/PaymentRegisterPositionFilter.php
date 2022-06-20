<?php


namespace App\Services\Filters;


class PaymentRegisterPositionFilter extends QueryFilter
{
    public function order_number(string $number)
    {
        return $this->builder->whereRelation('order', 'number', 'ILIKE', "%{$number}%");
    }

    public function order_date(string $from = null, string $to = null)
    {
        if (is_numeric(strtotime($from))) {
            $this->builder->whereRelation('order', 'order_date', '>=', $from);
        }
        if (is_numeric(strtotime($to))) {
            $this->builder->whereRelation('order', 'order_date', '<=', $to);
        }
        return $this->builder;
    }

    public function payment_order_date(string $from = null, string $to = null)
    {
        if (is_numeric(strtotime($from))) {
            $this->builder->whereDate('payment_order_date', '>=', $from);
        }
        if (is_numeric(strtotime($to))) {
            $this->builder->whereDate('payment_order_date', '<=', $to);
        }
        return $this->builder;
    }

    public function object(string $name)
    {
        return $this->builder->whereRelation('order.order_customers.object', 'name', 'ILIKE', "%{$name}%");
    }

    public function organization(string $name)
    {
        return $this->builder->whereRelation('order.order_customers.organization', 'name', 'ILIKE', "%{$name}%");
    }

    public function work_agreement_number(string $name)
    {
        return $this->builder->whereRelation('order.order_customers.work_agreement', 'number', 'ILIKE', "%{$name}%");
    }
}
