<?php

namespace Database\Seeders;

use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Nomenclature;
use App\Models\References\NomenclatureUnit;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
//        $order_info_data = [
//            'is_external' => false,
////            'number' => 19583,
//            'order_date' => Carbon::now()->format('d.m.Y'),
//            'deadline_date' => Carbon::tomorrow()->format('d.m.Y'),
//            'customer_status' => 'Согласовано',
//            'provider_status' => 'Черновик',
//            'customer_filial_branch' => 'Название филиала',
//            'work_agreement' => 'G-543534546',
//            'work_agreement_date' => Carbon::tomorrow()->format('d.m.Y'),
//            'work_type' => 'Строительство',
//            'object' => 'Название объекта',
//            'sub_object' => 'Название подобъекта',
//            'work_start_date' => Carbon::now()->format('d.m.Y'),
//            'work_end_date' => Carbon::now()->format('d.m.Y'),
//
//            'provider' => 'ООО «Рога и Копыта»',
//            'provider_contract' => 'ПП-000789',
//            'provider_contract_date' => Carbon::now()->format('d.m.Y'),
//            'provider_full_name' => 'Иванов Иван Иванович',
//            'provider_email' => 'mail@mail.ru',
//            'provider_phone' => '8 (950) 123-45-67',
//
//            'contractor' => 'ООО «Рога и копыта»',
//            'contractor_full_name' => 'Иванов Иван Иванович',
//            'contractor_email' => 'mail@mail.ru',
//            'contractor_phone' => '8 (950) 123-45-67',
//            'contractor_responsible_full_name' => 'Петров Пётр Петрович',
//            'contractor_responsible_phone' => '8 (950) 123-45-67',
//        ];
//
//        $order = Order::create($order_info_data);
//
//        $mtr = OrderPosition::create([
//            'order_info_id' => $order->id,
//            'status' => 'Согласовано',
//            'mnemocode' => '5345534545',
//            'nomenclature' => 'Лента крепления F207 0,7x20 мм L=50 м Niled',
//            'unit' => 'шт.',
//            'count' => 4343,
//            'price_without_vat' => 34.56,
//            'amount_without_vat' => 56.34,
//            'total_amount' => 554.54,
//            'delivery_time' => Carbon::tomorrow()->format('d.m.Y'),
//            'delivery_address' => '	г. Казань, ул. Пушкина, 100а',
//        ]);
//
//        $comment = Comment::create([
//            'user_id' => 1,
//            'text' => 'текст комментария',
//        ]);
//
//        $mtr->comments()->sync($comment->id);

        $organizations = [
            'Филиал СК №1',
            'Филиал СК №2',
            'Филиал СК №3',
            'Филиал СК №4',
            'Филиал СК №5',
        ];

        $work_agreements = [
            [
                'number' => 'П-434343434',
                'date' => Carbon::today()->subDays(rand(0, 365))
            ],
            [
                'number' => 'П-65445',
                'date' => Carbon::today()->subDays(rand(0, 365))
            ],
            [
                'number' => 'П-8989',
                'date' => Carbon::today()->subDays(rand(0, 365))
            ],
            [
                'number' => 'П-7556767',
                'date' => Carbon::today()->subDays(rand(0, 365))
            ],
        ];

        $objects = [
            [
                'name' => 'Объект №1',
                'sub_objects' => [
                    [
                        'name' => 'Подобъект №1.1'
                    ],
                    [
                        'name' => 'Подобъект №1.2'
                    ],
                    [
                        'name' => 'Подобъект №1.3'
                    ],
                ],
            ],
            [
                'name' => 'Объект №2',
                'sub_objects' => [
                    [
                        'name' => 'Подобъект №2.1'
                    ],
                    [
                        'name' => 'Подобъект №2.2'
                    ],
                    [
                        'name' => 'Подобъект №2.3'
                    ],
                ],
            ],
            [
                'name' => 'Объект №3',
                'sub_objects' => [
                    [
                        'name' => 'Подобъект №3.1'
                    ],
                    [
                        'name' => 'Подобъект №3.2'
                    ],
                    [
                        'name' => 'Подобъект №3.3'
                    ],
                ],
            ],
        ];

        $contr_agents = [
            [
                'name' => 'ООО "Рога и копыта"',
                'contacts' => [
                    [
                        'full_name' => 'Иванов Иван Иванович',
                        'email' => 'ivanov@ivanov.ru',
                        'phone' => '+99999999'
                    ],
                    [
                        'full_name' => 'Петров Петр Петрович',
                        'email' => 'petrov@petrov.ru',
                        'phone' => '+1111111'
                    ]
                ]
            ],
            [
                'name' => 'ООО "Пила и молоток"',
                'contacts' => [
                    [
                        'full_name' => 'Cим Сам Симович',
                        'email' => 'sim@sim.ru',
                        'phone' => '+222222222'
                    ],
                    [
                        'full_name' => 'Алексеев Алексей Алексеевич',
                        'email' => 'aleks@aleks.ru',
                        'phone' => '+333333333',
                    ]
                ]
            ],
            [
                'name' => 'ООО "Река и море"',
                'contacts' => [
                    [
                        'full_name' => 'Александров Алескандр Александрович',
                        'email' => 'san@san.ru',
                        'phone' => '+44444444444'
                    ],
                    [
                        'full_name' => 'Алексеев Алексей Алексеевич',
                        'email' => 'aleks@aleks.ru',
                        'phone' => '+55555555',
                    ]
                ]
            ],
            [
                'name' => 'ООО "Леса и поля"',
                'contacts' => [
                    [
                        'full_name' => 'Михаилов Михаил Михайлович',
                        'email' => 'mish@mish.ru',
                        'phone' => '+66666666'
                    ],
                    [
                        'full_name' => 'Матвееев Матвей Матвеевич',
                        'email' => 'mat@mat.ru',
                        'phone' => '+7777777777',
                    ]
                ]
            ],
        ];

        $provider_contracts = [
            [
                'number' => 'ПП-44444',
                'date' => Carbon::today()->subDays(rand(0, 365))
            ],
            [
                'number' => 'ПП-12121212',
                'date' => Carbon::today()->subDays(rand(0, 365))
            ],
            [
                'number' => 'ПП-00001',
                'date' => Carbon::today()->subDays(rand(0, 365))
            ],
            [
                'number' => 'ПП-87899899',
                'date' => Carbon::today()->subDays(rand(0, 365))
            ],
        ];

        $nomenclatures = [
            [
                'mnemocode' => "fd423434434",
                'name' => 'Лента крепления F207 0,7x20 мм L=50 м Niled',
                'units' => [
                    'кг',
                    'шт',
                ],
            ],
            [
                'mnemocode' => "ffd5455434",
                'name' => 'Гвозди длинные',
                'units' => [
                    'кг',
                    'шт',
                ],
            ],
            [
                'mnemocode' => "qqq3234356",
                'name' => 'Раковина с тумбой',
                'units' => [
                    'шт',
                ],
            ],
            [
                'mnemocode' => "gf34dgh6f",
                'name' => 'Цемент строительный',
                'units' => [
                    'кг',
                    'л',
                    'шт',
                ],
            ],
        ];

        DB::transaction(function () use ($organizations, $provider_contracts, $work_agreements, $contr_agents, $objects, $nomenclatures) {
            foreach ($organizations as $organization)
                Organization::query()->create(['name' => $organization]);

            foreach ($work_agreements as $work_agreement) {
                WorkAgreementDocument::query()->create($work_agreement);
            }

            foreach ($objects as $object) {
                $customer_object = CustomerObject::query()->create(['name' => $object['name']]);
                foreach ($object['sub_objects'] as $sub_object)
                    $customer_object->subObjects()->create($sub_object);
            }

            foreach ($contr_agents as $contr_agent) {
                $contr_agent_model = ContrAgent::query()->create(['name' => $contr_agent['name']]);
                foreach ($contr_agent['contacts'] as $contact) {
                    $contr_agent_model->contacts()->create($contact);
                }
            }

            foreach ($provider_contracts as $provider_contract)
                ProviderContractDocument::query()->create($provider_contract);

            foreach ($nomenclatures as $nomenclature) {
                $nomenclature_model = Nomenclature::query()->create([
                    'mnemocode' => $nomenclature['mnemocode'],
                    'name' => $nomenclature['name'],
                ]);

                dump($nomenclature);
                foreach ($nomenclature['units'] as $unit) {
                    $unit = NomenclatureUnit::query()->firstOrCreate([
                        'name' => $unit,
                    ]);
                    $nomenclature_model->units()->attach($unit->id);
                }
            }
        });
    }
}
