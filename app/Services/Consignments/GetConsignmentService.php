<?php


namespace App\Services\Consignments;


use App\Models\Consignments\Consignment;
use App\Services\IService;

class GetConsignmentService implements IService
{
    public function __construct(private $payload, private Consignment $consignment)
    {
    }

    public function run()
    {
        $consignment = $this->consignment->newQuery()
            ->with([
                'provider',
                'contractor',
                'work_agreement',
                'provider_contract',
            ])/*->with([
            'order',
            'order.customer.contract',
            'order.customer.organization',
            'order.customer.object',
            'order.customer.subObject',
            'order.provider.contact.contrAgentName',
            'order.provider.contract',
            'order.contractor.contact.contrAgentName',
        ])*/ ->first();

        return $consignment;
    }
}
