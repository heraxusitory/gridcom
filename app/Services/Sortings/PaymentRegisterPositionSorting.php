<?php


namespace App\Services\Sortings;


class PaymentRegisterPositionSorting extends QuerySorting
{

    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    protected function number()
    {
        return $this->builder->orderBy('id', $this->order);
    }

    public function order_number()
    {
        return $this->builder
            ->leftJoin('orders', 'payment_registers.order_id', '=', 'orders.id')
            ->orderBy('orders.number', $this->order);
    }

    public function order_date()
    {
        return $this->builder
            ->leftJoin('orders', 'payment_registers.order_id', '=', 'orders.id')
            ->orderBy('orders.order_date', $this->order);
    }

    protected function payment_order_number()
    {
        return $this->builder->orderBy('payment_order_number', $this->order);
    }

    protected function payment_order_date()
    {
        return $this->builder->orderBy('payment_order_date', $this->order);
    }

    protected function amount_payment()
    {
        return $this->builder->orderBy('amount_payment', $this->order);
    }

    protected function payment_type()
    {
        return $this->builder->orderBy('payment_type', $this->order);
    }
}
