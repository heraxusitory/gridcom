<?php

namespace Database\Seeders;

use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProviderOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $faker = Factory::create();
            $count = 10;
            $organization_ids = Organization::pluck('id');
            $nomenclature_ids = Nomenclature::pluck('id');
            for ($i = 0; $i < $count; $i++) {
                $provider_order = ProviderOrder::query()->create([
                    'uuid' => Str::uuid(),
                    'number' => $faker->numberBetween(432432423, 554546563),
                    'order_date' => $faker->date('d.m.Y'),
                    'contract_number' => 'Д-' . $faker->numberBetween(34454543, 2444332222),
                    'contract_date' => $faker->date('d.m.Y'),
                    'contract_stage' => $faker->numberBetween(1, 7),
                    'provider_contr_agent_id' => $faker->numberBetween(1, 4),
                    'organization_id' => $organization_ids->random(),
                    'responsible_full_name' => $faker->name . ' ' . $faker->lastName,
                    'responsible_phone' => $faker->phoneNumber,
                    'organization_comment' => $faker->realText(240)
                ]);

                $provider_order->base_positions()->create([
                    'position_id' => Str::uuid(),
                    'nomenclature_id' => $nomenclature_ids->random(),
                    'count' => $faker->randomFloat(),
                    'price_without_vat' => $faker->randomFloat(),
                    'amount_without_vat' => $faker->randomFloat(),
                    'amount_with_vat' => $faker->randomFloat(),
                    'vat_rate' => $faker->randomFloat(null, 1, 2),
                    'delivery_time' => $faker->date('d.m.Y'),
                    'delivery_address' => $faker->address,
                    'organization_comment' => $faker->realText(233),
                ]);

                $provider_order->actual_positions()->create([
                    'position_id' => Str::uuid(),
                    'nomenclature_id' => $nomenclature_ids->random(),
                    'count' => $faker->randomFloat(),
                    'price_without_vat' => $faker->randomFloat(),
                    'vat_rate' => $faker->randomFloat(null, 1, 2),
                    'amount_with_vat' => $faker->randomFloat(),
                    'amount_without_vat' => $faker->randomFloat(),
                    'delivery_time' => $faker->date('d.m.Y'),
                    'delivery_address' => $faker->address,
                    'organization_comment' => $faker->realText(233),
                ]);

                $requirement_correction = $provider_order->requirement_corrections()->create([
                    'correction_id' => Str::uuid(),
                    'date' => $faker->date('d.m.Y'),
                    'number' => 'К-' . $faker->numberBetween(1, 20),
                    'provider_status' => 'На рассмотрении',
                ]);

                $requirement_correction->positions()->create([
                    'position_id' => Str::uuid(),
                    'status' => 'На рассмотрении',
                    'nomenclature_id' => $nomenclature_ids->random(),
                    'count' => $faker->randomFloat(),
                    'amount_without_vat' => $faker->randomFloat(),
                    'vat_rate' => $faker->randomFloat(null, 1, 2),
                    'amount_with_vat' => $faker->randomFloat(),
                    'delivery_time' => $faker->date('d.m.Y'),
                    'delivery_address' => $faker->address,
                    'organization_comment' => $faker->realText(200),
                ]);

                $order_correction = $provider_order->order_corrections()->create([
                    'correction_id' => Str::uuid(),
                    'date' => $faker->date('d.m.Y'),
                    'number' => 'К-' . $faker->numberBetween(1, 20),
                ]);

                $order_correction->positions()->create([
                    'position_id' => Str::uuid(),
                    'nomenclature_id' => $nomenclature_ids->random(),
                    'count' => $faker->randomFloat(),
                    'amount_without_vat' => $faker->randomFloat(),
                    'vat_rate' => $faker->randomFloat(null, 1, 2),
                    'amount_with_vat' => $faker->randomFloat(),
                    'delivery_time' => $faker->date('d.m.Y'),
                    'delivery_address' => $faker->address,
                ]);
            }
        });
    }
}
