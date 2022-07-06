<?php


namespace App\Services\Sortings\ProviderOrders;


use App\Services\Sortings\QuerySorting;

class RequirementCorrectionSorting extends QuerySorting
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

    protected function requirement_correction_number()
    {
        return $this->builder->orderBy('number', $this->order);
    }

    protected function provider_status()
    {
        return $this->builder->orderBy('provider_status', $this->order);
    }
}
