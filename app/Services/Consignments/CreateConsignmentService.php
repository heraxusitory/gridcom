<?php


namespace App\Services\Consignments;


use App\Models\Consignments\Consignment;
use App\Models\References\Nomenclature;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateConsignmentService implements IService
{
    public function __construct(private $payload)
    {
    }

    public function run()
    {
        $data = $this->payload;
        return DB::transaction(function () use ($data) {
            /** @var Consignment $consignment */
            $consignment = Consignment::query()->create([
                'uuid' => Str::uuid(),
                'date' => Carbon::today()->format('d.m.Y'),
                'organization_id' => $data['organization_id'],
                'provider_contr_agent_id' => $data['provider_contr_agent_id'],
                'provider_contract_id' => $data['provider_contract_id'],
                'contractor_contr_agent_id' => $data['contractor_contr_agent_id'],
                'work_agreement_id' => $data['work_agreement_id'],
                'customer_object_id' => $data['customer_object_id'],
                'customer_sub_object_id' => $data['customer_sub_object_id'],
//                'order_id' => $data['order_id'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
            ]);

            foreach ($data['positions'] as $position) {
                $nomenclature = Nomenclature::query()->findOrFail($position['nomenclature_id']);
                $amount_without_vat = round($nomenclature->price * $position['count'], 2);
                $amount_with_vat = round($amount_without_vat * $position['vat_rate'], 2);
                $consignment->positions()->create([
                    'position_id' => Str::uuid(),
                    'order_id' => $position['order_id'],
                    'nomenclature_id' => $position['nomenclature_id'],
                    'count' => $position['count'],
                    'price_without_vat' => $nomenclature->price,
                    'amount_without_vat' => $amount_without_vat,
                    'vat_rate' => $position['vat_rate'],
                    'amount_with_vat' => $amount_with_vat,
                    'country' => $position['country'],
                    'cargo_custom_declaration' => $position['cargo_custom_declaration'],
                    'declaration' => $position['declaration'],
                ]);
            }
            return $consignment;
        });

    }
}
