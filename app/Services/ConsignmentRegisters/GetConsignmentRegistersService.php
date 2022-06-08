<?php


namespace App\Services\ConsignmentRegisters;


use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Services\Filters\ConsignmentRegisterFilter;
use App\Services\IService;
use App\Services\Sortings\ConsignmentRegisterSorting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class GetConsignmentRegistersService implements IService
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct(private $payload, private ConsignmentRegisterFilter $filter, private ConsignmentRegisterSorting $sorting)
    {
        $this->user = Auth::user();
    }

    public function run()
    {
        $consignment_registers = ConsignmentRegister::query()
            ->filter($this->filter)
            ->with(['positions.nomenclature']);/*->get();*/
        if ($this->user->isProvider()) {
            $consignment_registers->where('provider_contr_agent_id', $this->user->contr_agent_id());
        } elseif ($this->user->isContractor()) {
            $consignment_registers->where('contractor_contr_agent_id', $this->user->contr_agent_id());
        }

//        $consignment_registers = $consignment_registers->get()->map(function ($consignment_register) {
//            $consignment_register->positions->map(function ($position) {
//                $position->amount_without_vat =
//                    round($position->count * $position->nomenclature->price, 2);
//                $position->amount_with_vat = round($position->amount_without_vat * $position->vat_rate);
//                return $position;
//            });
//            return $consignment_register;
//        });

        return $consignment_registers->sorting($this->sorting)->get();
//        return (new Paginator($consignment_registers, 15));
    }
}
