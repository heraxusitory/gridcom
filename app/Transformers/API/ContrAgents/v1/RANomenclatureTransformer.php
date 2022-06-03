<?php

namespace App\Transformers\API\ContrAgents\v1;

use App\Models\RequestAdditions\RequestAdditionNomenclature;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class RANomenclatureTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(RequestAdditionNomenclature $ra_nomenclature)
    {
        return [
            'id' => $ra_nomenclature?->uuid,
            'type' => $ra_nomenclature->type,
            'number' => $ra_nomenclature?->number,
            'date' => $ra_nomenclature?->date,
            'contr_agent_id' => optional($ra_nomenclature->contr_agent)->uuid,
            'work_agreement_id' => optional($ra_nomenclature->work_agreement)->uuid,
            'provider_contract_id' => optional($ra_nomenclature->provider_contract)->uuid,
            'organization_id' => optional($ra_nomenclature->organization)->uuid,
            'organization_status' => $ra_nomenclature->organization_status,
            'nomenclature_id' => optional($ra_nomenclature->nomenclature)->uuid,
            'nomenclature_name' => $ra_nomenclature?->nomenclature_name,
            'nomenclature_unit' => $ra_nomenclature?->nomenclature_unit,
            'description' => $ra_nomenclature?->description,
            'responsible_full_name' => $ra_nomenclature?->responsible_full_name,
            'contr_agent_comment' => $ra_nomenclature?->contr_agent_comment,
            'organization_comment' => $ra_nomenclature?->organization_comment,
            'file_exists' => /*$ra_nomenclature?->file_url ? Storage::url($ra_nomenclature->file_url) : $ra_nomenclature?->file_url*/Storage::exists($ra_nomenclature->file_url)
        ];
    }
}
