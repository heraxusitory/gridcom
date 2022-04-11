<?php


namespace App\Services\Consignments;


use App\Models\Consignments\Consignment;
use App\Services\IService;
use Illuminate\Support\Facades\Auth;

class GetConsignmentsService implements IService
{
    public function __construct(private $payload)
    {
        $this->user = Auth::user();
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
        ]);
        if ($this->user->isProvider()) {
            $consignments->whereRelation('order.provider', 'contr_agent_id', $this->user->contr_agent_id());
        } elseif ($this->user->isContractor()) {
            $consignments->whereRelation('order.contractor', 'contr_agent_id', $this->user->contr_agent_id());
        }
        return $consignments->paginate();
    }

}
