<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\RequestAdditions\RequestAdditionObject;
use League\Fractal\TransformerAbstract;

class RAObjectTransformer extends TransformerAbstract
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
    public function transform(RequestAdditionObject $object)
    {
        return [
            'id' => $object?->uuid,
            'number' => $object?->number,
            'date' => $object?->date,
            'contr_agent_id' => optional($object->contr_agent)->uuid,
            'work_agreement_id' => optional($object->work_agreement)->uuid,
            'provider_contract_id' => optional($object->provider_contract)->uuid,
            'organization_id' => optional($object->organization)->uuid,
            'organization_status' => $object?->organization_status,
            'object_id' => optional($object->object)->uuid,
            'description' => $object?->description,
            'responsible_full_name' => $object?->responsible_full_name,
            'contr_agent_comment' => $object?->contr_agent_comment,
            'organization_comment' => $object?->organization_comment,
            'file_url' => $object?->file_url,
        ];
    }
}
