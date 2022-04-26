<?php

namespace App\Transformers\API\MTO\v1;

use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\ConsignmentRegisters\ConsignmentRegisterPosition;
use League\Fractal\TransformerAbstract;

class ConsignmentRegisterTransformer extends TransformerAbstract
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
    public function transform(ConsignmentRegister $consignment_register)
    {
        return [
            'id' => $consignment_register?->uuid,
            'number' => $consignment_register?->number,
            'customer_status' => $consignment_register?->customer_status,
            'contr_agent_status' => $consignment_register?->contr_agent_status,
            'organization_id' => $consignment_register->organization->uuid,
            'contractor_contr_agent_id' => optional($consignment_register->contractor)->uuid,
            'provider_contr_agent_id' => optional($consignment_register->provider)->uuid,
            'customer_object_id' => optional($consignment_register->object)->uuid,
            'customer_sub_object_id' => optional($consignment_register->subObject)->uuid,
            'work_agreement_id' => optional($consignment_register->work_agreement)->uuid,
            'responsible_full_name' => $consignment_register?->responsible_full_name,
            'responsible_phone' => $consignment_register?->responsible_phone,
            'comment' => $consignment_register?->comment,
            'date' => $consignment_register?->date,
        ];
    }

    public function includePositions(ConsignmentRegister $consignment_register)
    {
        if ($consignment_register?->positions())
            return $this->collection($consignment_register->positions, new ConsignmentRegisterPositionTransformer());
        return $this->null();
    }
}
