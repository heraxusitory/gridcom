<?php


namespace App\Services\ConsignmentNotes;


use App\Models\ConsignmentNotes\ConsignmentNote;
use App\Services\IService;

class GetConsignmentService implements IService
{
    public function __construct(private $payload)
    {
    }

    public function run()
    {
        $consignments = ConsignmentNote::query()->with([
            'order',
            'order.customer.contract',
            'order.customer.organization',
            'order.customer.object',
            'order.customer.subObject',
            'order.provider.contact.contrAgentName',
            'order.provider.contract',
            'order.contractor.contact.contrAgentName',
        ])->get();
        return $consignments;
    }
}
