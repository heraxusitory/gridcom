<?php


namespace App\Services\Sortings\ProviderOrders;


use App\Services\Sortings\QuerySorting;

class RequirementCorrectionPositionSorting extends QuerySorting
{

    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    protected function number()
    {
        return $this->builder->orderBy('id', $this->order);
    }

    protected function status()
    {
        return $this->builder->orderBy('status', $this->order);
    }

    public function nomenclature_name()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'requirement_correction_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.name', $this->order);
    }

    public function mnemocode()
    {
        return $this->builder
            ->leftJoin('nomenclature', 'requirement_correction_positions.nomenclature_id', '=', 'nomenclature.id')
            ->orderBy('nomenclature.mnemocode', $this->order);
    }

    public function count()
    {
        return $this->builder->orderBy('count', $this->order);
    }

    public function price_without_vat()
    {
        return $this->builder->orderBy('price_without_vat', $this->order);
    }

    public function delta()
    {
        return $this->builder->orderBy('delta', $this->order);
    }

    public function amount_without_vat()
    {
        return $this->builder->orderBy('amount_without_vat', $this->order);
    }

    public function vat_rate()
    {
        return $this->builder->orderBy('vat_rate', $this->order);
    }

    public function amount_with_vat()
    {
        return $this->builder->orderBy('amount_with_vat', $this->order);
    }

    public function delivery_time()
    {
        return $this->builder->orderBy('delivery_time', $this->order);
    }

    public function delivery_address()
    {
        return $this->builder->orderBy('delivery_address', $this->order);
    }

    public function organization_comment()
    {
        return $this->builder->orderBy('organization_comment', $this->order);
    }

    public function provider_comment()
    {
        return $this->builder->orderBy('provider_comment', $this->order);
    }
}
