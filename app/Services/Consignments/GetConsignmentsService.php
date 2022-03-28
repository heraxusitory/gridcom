<?php


namespace App\Services\Consignments;


use App\Models\Consignments\Consignment;
use App\Services\IService;

class GetConsignmentsService implements IService
{
    public function __construct(private $payload)
    {
    }

    public function run()
    {
        $consignments = Consignment::query()->with([
            'order',
            'order.customer.contract',
            'order.customer.organization',
            'order.customer.object',
            'order.customer.subObject',
            'order.provider.contact.contrAgentName',
            'order.provider.contract',
            'order.contractor.contact.contrAgentName',
        ])->paginate();
        return $consignments;
    }

}
