<?php

namespace Database\Seeders;

use App\Models\Comments\Comment;
use App\Models\OrderPositions\OrderPosition;
use App\Models\Orders\Order;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Psy\Util\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = new Generator();
        $order_info_data = [
            'is_external' => false,
//            'number' => 19583,
            'order_date' => Carbon::now()->format('d.m.Y'),
            'deadline_date' => Carbon::tomorrow()->format('d.m.Y'),
            'customer_status' => 'Согласовано',
            'provider_status' => 'Черновик',
            'customer_filial_branch' => 'Название филиала',
            'work_agreement' => 'G-543534546',
            'work_agreement_date' => Carbon::tomorrow()->format('d.m.Y'),
            'work_type' => 'Строительство',
            'object' => 'Название объекта',
            'sub_object' => 'Название подобъекта',
            'work_start_date' => Carbon::now()->format('d.m.Y'),
            'work_end_date' => Carbon::now()->format('d.m.Y'),

            'provider' => 'ООО «Рога и Копыта»',
            'provider_contract' => 'ПП-000789',
            'provider_contract_date' => Carbon::now()->format('d.m.Y'),
            'provider_full_name' => 'Иванов Иван Иванович',
            'provider_email' => 'mail@mail.ru',
            'provider_phone' => '8 (950) 123-45-67',

            'contractor' => 'ООО «Рога и копыта»',
            'contractor_full_name' => 'Иванов Иван Иванович',
            'contractor_email' => 'mail@mail.ru',
            'contractor_phone' => '8 (950) 123-45-67',
            'contractor_responsible_full_name' => 'Петров Пётр Петрович',
            'contractor_responsible_phone' => '8 (950) 123-45-67',
        ];

        $order = Order::create($order_info_data);

        $mtr = OrderPosition::create([
            'order_info_id' => $order->id,
            'status' => 'Согласовано',
            'mnemocode' => '5345534545',
            'nomenclature' => 'Лента крепления F207 0,7x20 мм L=50 м Niled',
            'unit' => 'шт.',
            'count' => 4343,
            'price_without_vat' => 34.56,
            'amount_without_vat' => 56.34,
            'total_amount' => 554.54,
            'delivery_time' => Carbon::tomorrow()->format('d.m.Y'),
            'delivery_address' => '	г. Казань, ул. Пушкина, 100а',
        ]);

        $comment = Comment::create([
            'user_id' => 1,
            'text' => 'текст комментария',
        ]);

        $mtr->comments()->sync($comment->id);
    }
}
