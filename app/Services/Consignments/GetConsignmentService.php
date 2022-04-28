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
                'positions',
                'provider',
                'contractor',
                'work_agreement',
                'provider_contract',
            ])->first();

        return $consignment;
    }
}
