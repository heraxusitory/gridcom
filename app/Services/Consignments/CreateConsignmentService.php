<?php


namespace App\Services\Consignments;


use App\Models\Consignments\Consignment;
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
            $consignment = Consignment::query()->create([
                'uuid' => Str::uuid(),
                'date' => Carbon::today()->format('d.m.Y'),
                'order_id' => $data['order_id'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
            ]);

            foreach ($data['positions'] as $position) {
                $amount_without_vat = round($position['price_without_vat'] * $position['count'], 2);
                $amount_with_vat = round($amount_without_vat * $position['vat_rate'], 2);
                $consignment->positions()->create([
                    'position_id' => Str::uuid(),
                    'nomenclature_id' => $position['nomenclature_id'],
                    'unit_id' => $position['unit_id'],
                    'count' => $position['count'],
                    'price_without_vat' => $position['price_without_vat'],
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
