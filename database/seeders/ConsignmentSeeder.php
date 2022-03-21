<?php

namespace Database\Seeders;

use App\Models\Consignments\Consignment;
use App\Models\Orders\LKK\Order;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConsignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        $order_ids = Order::query()->pluck('id');

        foreach ($order_ids as $order_id) {
            $consignment_data = [
                'number' => Str::uuid(),
                'date' => Carbon::today(),
                'order_id' => $order_id,
                'responsible_full_name' => $faker->firstName . ' ' . $faker->lastName,
                'responsible_phone' => $faker->phoneNumber,
                'comment' => $faker->realText(200),
            ];
            Consignment::query()->create($consignment_data);
        }
    }
}
