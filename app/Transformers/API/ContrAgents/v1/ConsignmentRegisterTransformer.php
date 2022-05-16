<?php

namespace App\Transformers\API\ContrAgents\v1;

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
            'organization' => [
                'name' => optional($consignment_register?->organization)->name,
            ],
            'contractor_contr_agent' => [
                'name' => optional($consignment_register?->contractor)->name,
            ],
            'provider_contr_agent' => [
                'name' => optional($consignment_register?->provider)->name
            ],
            'customer_object' => [
                'name' => optional($consignment_register?->object)->name
            ],
            'customer_sub_object' => [
                'name' => optional($consignment_register?->subObject)->name
            ],
            'work_agreement' => [
                'number' => optional($consignment_register?->work_agreement)->number
            ],
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
