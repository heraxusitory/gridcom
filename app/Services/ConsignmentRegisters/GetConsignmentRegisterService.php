<?php


namespace App\Services\ConsignmentRegisters;


use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Services\IService;
use Illuminate\Pagination\Paginator;

class GetConsignmentRegisterService implements IService
{
    public function __construct(private $paylaod, private ConsignmentRegister $consignment_register)
    {
    }

    public function run()
    {
//        $this->consignment_register->positions->map(function ($position) {
//            $position->amount_without_vat =
//                round($position->count * $position->nomenclature->price, 2);
//            $position->amount_with_vat = round($position->amount_without_vat * $position->vat_rate);
//        });

        return $this->consignment_register;
    }
}
