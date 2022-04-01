<?php


namespace App\Services\Consignments;


use App\Models\Consignments\Consignment;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\References\Nomenclature;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateConsignmentService implements IService
{
    public function __construct(private $payload, private Consignment $consignment)
    {
    }

    public function run()
    {
        $data = $this->payload;

        $this->consignment->is_approved = match ($data['action']) {
            PaymentRegister::ACTION_DRAFT => false,
            PaymentRegister::ACTION_APPROVE => true,
            default => throw new BadRequestException('Action is required', 400),
        };

        return DB::transaction(function () use ($data) {
            $this->consignment->update([
                'date' => Carbon::today()->format('d.m.Y'),
                'order_id' => $data['order_id'],
                'responsible_full_name' => $data['responsible_full_name'],
                'responsible_phone' => $data['responsible_phone'],
                'comment' => $data['comment'],
            ]);

            $position_ids = [];
            foreach ($data['positions'] as $position) {
                $nomenclature = Nomenclature::query()->findOrFail($position['nomenclature_id']);
                $amount_without_vat = round($nomenclature->price * $position['count'], 2);
                $amount_with_vat = round($amount_without_vat * $position['vat_rate'], 2);
                $position = $this->consignment->positions()->updateOrCreate([
                    'position_id' => $position['position_id'] ?? null
                ], [
                    'position_id' => $position['position_id'] ?? Str::uuid(),
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
                $position_ids[] = $position->id;
            }

            $this->consignment->positions()->whereNotIn('id', $position_ids)->delete();

            return $this->consignment;
        });
    }
}
