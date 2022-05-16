<?php

namespace App\Transformers\API\ContrAgents\v1;

use App\Models\Consignments\Consignment;
use League\Fractal\TransformerAbstract;

class ConsignmentTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'positions'
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
    public function transform(Consignment $consignment)
    {
        return [
            'id' => $consignment?->uuid,
            'number' => $consignment?->number,
            'date' => $consignment?->date,
            'organization_id' => optional($consignment?->organization)->uuid,
            'provider_contr_agent_id' => optional($consignment?->provider)->uuid,
            'provider_contract_id' => optional($consignment?->provider_contract)->uuid,
            'contractor_contr_agent_id' => optional($consignment?->contractor)->uuid,
            'work_agreement_id' => optional($consignment?->work_agreement)->uuid,
            'customer_object_id' => optional($consignment?->object)->uuid,
            'customer_sub_object_id' => optional($consignment?->subObject)->uuid,
            'responsible_full_name' => $consignment?->responsible_full_name,
            'responsible_phone' => $consignment?->responsible_phone,
            'comment' => $consignment?->comment,


        ];
    }

    public function includePositions(Consignment $consignment)
    {
        if ($consignment?->positions)
            return $this->collection($consignment->positions, new ConsignmentPositionTransformer());
        return $this->null();
    }
}
