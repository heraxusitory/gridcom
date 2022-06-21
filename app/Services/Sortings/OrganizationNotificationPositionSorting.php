<?php


namespace App\Services\Sortings;


class OrganizationNotificationPositionSorting extends QuerySorting
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
            ->leftJoin('provider_orders', 'organization_notification_positions.order_id', '=', 'provider_orders.id')
            ->orderBy('provider_orders.number', $this->order);
    }

    public function order_date()
    {
        return $this->builder
            ->leftJoin('provider_orders', 'organization_notification_positions.order_id', '=', 'provider_orders.id')
            ->orderBy('provider_orders.order_date', $this->order);
    }

    public function mnemocode()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'organization_notification_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.mnemocode', $this->order);
    }

    public function nomenclature_name()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'organization_notification_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.name', $this->order);
    }

    protected function count()
    {
        return $this->builder->orderBy('count', $this->order);
    }

    protected function vat_rate()
    {
        return $this->builder->orderBy('vat_rate', $this->order);
    }

    protected function price_without_vat()
    {
        return $this->builder->orderBy('price_without_vat', $this->order);
    }

    public function amount_without_vat()
    {
        return $this->builder
            ->selectRaw('*, (price_without_vat * count) as amount_without_vat')
            ->orderBy('amount_without_vat', $this->order);
    }

    public function amount_with_vat()
    {
        return $this->builder
            ->selectRaw('*, (price_without_vat * count * vat_rate) as amount_with_vat')
            ->orderBy('amount_with_vat', $this->order);
    }
}
