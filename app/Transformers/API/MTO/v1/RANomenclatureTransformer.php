<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\RequestAdditions\RequestAdditionNomenclature;
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
            'number' => $ra_nomenclature?->number,
            'date' => $ra_nomenclature?->date,
            'contr_agent_id' => optional($ra_nomenclature->contr_agent)->uuid,
            'work_agreement_id' => optional($ra_nomenclature->work_agreement)->uuid,
            'provider_contract_id' => optional($ra_nomenclature->provider_contract)->uuid,
            'organization_id' => optional($ra_nomenclature->organization)->uuid,
            'organization_status' => $ra_nomenclature->organization_status,
            'nomenclature_id' => optional($ra_nomenclature->nomenclature)->uuid,
            'description' => $ra_nomenclature?->description,
            'responsible_full_name' => $ra_nomenclature?->responsible_full_name,
            'contr_agent_comment' => $ra_nomenclature?->contr_agent_comment,
            'organization_comment' => $ra_nomenclature?->organization_comment,
            'file_url' => $ra_nomenclature?->file_url,
        ];
    }
}
