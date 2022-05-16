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
            'organization' => ['name' => optional($consignment?->organization)->name],
            'provider_contr_agent' => ['name' => optional($consignment?->provider)->name],
            'provider_contract' => ['number' => optional($consignment?->provider_contract)->number],
            'contractor_contr_agent' => ['name' => optional($consignment?->contractor)->name],
            'work_agreement' => ['number' => optional($consignment?->work_agreement)->number],
            'customer_object' => ['name' => optional($consignment?->object)->name],
            'customer_sub_object' => ['name' => optional($consignment?->subObject)->name],
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
