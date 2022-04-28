<?php

namespace App\Transformers\WebAPI\v1;

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
    public function transform(Consignment $consignment)
    {
        $provider = $consignment?->provider;
        if ($provider)
            $provider->contract = $consignment?->provider_contract;
        return [
            'number' => $consignment?->number,
            'organization_id' => $consignment?->organization_id,
            'provider_contr_agent_id' => $consignment?->provider_contr_agent_id,
            'provider' => $provider,
            'contractor_contr_agent_id' => $consignment?->contractor_contr_agent_id,
            'contractor' => $consignment?->contractor,
            'work_agreement_id' => $consignment?->work_agreement_id,
            'work_agreement' => $consignment?->work_agreement,
            'customer_object_id' => $consignment?->customer_object_id,
            'customer_object' => $consignment?->object,
            'customer_sub_object_id' => $consignment?->customer_sub_object_id,
            'customer_sub_object' => $consignment?->subObject,
            'date' => $consignment?->date,
            'responsible_full_name' => $consignment?->responsible_full_name,
            'responsible_phone' => $consignment?->responsible_phone,
            'comment' => $consignment?->comment,
        ];
    }
}
