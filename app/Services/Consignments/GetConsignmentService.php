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
        /** @var Consignment $consignment */
        $consignment = $this->consignment
            ->with([
                'positions.order',
                'positions.nomenclature',
                'provider',
                'contractor',
                'work_agreement',
                'provider_contract',
            ])
            ->withSum('positions', 'amount_without_vat')
            ->withSum('positions', 'amount_with_vat');

        return $consignment;
    }
}
