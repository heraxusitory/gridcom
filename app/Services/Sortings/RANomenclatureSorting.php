<?php


namespace App\Services\Sortings;


use Illuminate\Support\Facades\DB;

class RANomenclatureSorting extends QuerySorting
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

    public function description()
    {
        return $this->builder->orderBy('description', $this->order);
    }

    public function responsible_full_name()
    {
        return $this->builder->orderBy('responsible_full_name', $this->order);
    }

    public function contr_agent_comment()
    {
        return $this->builder->orderBy('contr_agent_comment', $this->order);
    }

    public function organization_comment()
    {
        return $this->builder->orderBy('organization_comment', $this->order);
    }

    public function organization_status()
    {
        return $this->builder->orderBy('organization_status', $this->order);
    }

    public function nomenclature()
    {
        return $this->builder
            ->select('request_addition_nomenclatures.*', DB::raw("
                (CASE request_addition_nomenclatures.type
                    WHEN 'change'
                    THEN (select nomenclature.name FROM nomenclature
                        WHERE
                             request_addition_nomenclatures.nomenclature_id = nomenclature.id
                        )
                    WHEN 'new'
                    THEN (select nomenclature_name)
                    ELSE null
                END) as ra_nomenclature_name"
            ))
            ->orderBy('ra_nomenclature_name', $this->order);
    }

    public function nomenclature_unit()
    {
        return $this->builder
            ->select('request_addition_nomenclatures.*', DB::raw("
               (CASE request_addition_nomenclatures.type
                    WHEN 'change'
                    THEN (select nomenclature_units.name FROM nomenclature
                        JOIN nomenclature_to_unit ON nomenclature.id = nomenclature_to_unit.nomenclature_id
                        JOIN nomenclature_units ON nomenclature_to_unit.unit_id = nomenclature_units.id
                        WHERE request_addition_nomenclatures.nomenclature_id = nomenclature.id LIMIT 1
                        )
                    WHEN 'new'
                    THEN (select nomenclature_unit)
                    ELSE null
                END) as ra_nomenclature_unit"
            ))
            ->orderBy('ra_nomenclature_unit', $this->order);
    }

    public function organization()
    {
        return $this->builder
            ->leftJoin('organizations', 'request_addition_nomenclatures.organization_id', '=', 'organizations.id')
            ->orderBy('organizations.name', $this->order);
    }

    public function work_agreement_number()
    {
        return $this->builder
            ->leftJoin('work_agreements', 'request_addition_nomenclatures.work_agreement_id', '=', 'work_agreements.id')
            ->orderBy('work_agreements.number', $this->order);
    }

    public function work_agreement_date()
    {
        return $this->builder
            ->leftJoin('work_agreements', 'request_addition_nomenclatures.work_agreement_id', '=', 'work_agreements.id')
            ->orderBy('work_agreements.date', $this->order);
    }

    public function provider_contract_number()
    {
        return $this->builder
            ->leftJoin('provider_contracts', 'request_addition_nomenclatures.provider_contract_id', '=', 'provider_contracts.id')
            ->orderBy('provider_contracts.number', $this->order);
    }

    public function provider_contract_date()
    {
        return $this->builder
            ->leftJoin('provider_contracts', 'request_addition_nomenclatures.provider_contract_id', '=', 'provider_contracts.id')
            ->orderBy('provider_contracts.date', $this->order);
    }
}
