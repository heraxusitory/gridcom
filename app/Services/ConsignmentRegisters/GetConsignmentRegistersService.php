<?php


namespace App\Services\ConsignmentRegisters;


use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Services\IService;
use Illuminate\Pagination\Paginator;

class GetConsignmentRegistersService implements IService
{
    public function __construct()
    {
    }

    public function run()
    {
        $consignment_registers = ConsignmentRegister::query()
            ->with(['positions.nomenclature'])->get();

        $consignment_registers->map(function ($consignment_register) {
            return $consignment_register->positions->map(function ($position) {
                $position->amount_without_vat =
                    round($position->count * $position->nomenclature->price, 2);
                $position->amount_with_vat = round($position->amount_without_vat * $position->vat_rate);
            });
        });

        return (new Paginator($consignment_registers, 15));
    }
}
