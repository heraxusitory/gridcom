<?php

namespace Database\Seeders;

use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Nomenclature;
use App\Models\References\NomenclatureUnit;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
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
                'price' => 32434.23
            ],
            [
                'mnemocode' => "ffd5455434",
                'name' => 'Гвозди длинные',
                'units' => [
                    'кг',
                    'шт',
                ],
                'price' => 32434.23

            ],
            [
                'mnemocode' => "qqq3234356",
                'name' => 'Раковина с тумбой',
                'units' => [
                    'шт',
                ],
                'price' => 32434.23

            ],
            [
                'mnemocode' => "gf34dgh6f",
                'name' => 'Цемент строительный',
                'units' => [
                    'кг',
                    'л',
                    'шт',
                ],
                'price' => 32434.23
            ],
        ];

        DB::transaction(function () use ($organizations, $provider_contracts, $work_agreements, $contr_agents, $objects, $nomenclatures) {
            foreach ($organizations as $organization)
                Organization::query()->create(['name' => $organization, 'uuid' => Str::uuid()]);

            foreach ($work_agreements as $work_agreement) {
                $work_agreement['uuid'] = Str::uuid();
                WorkAgreementDocument::query()->create($work_agreement);
            }

            foreach ($objects as $object) {
                $customer_object = CustomerObject::query()->create(['name' => $object['name'], 'uuid' => Str::uuid()]);
                foreach ($object['sub_objects'] as $sub_object) {
                    $sub_object['uuid'] = Str::uuid();
                    $customer_object->subObjects()->create($sub_object);
                }
            }

            foreach ($contr_agents as $contr_agent) {
                $contr_agent_model = ContrAgent::query()->create(['name' => $contr_agent['name'], 'uuid' => Str::uuid()]);
                foreach ($contr_agent['contacts'] as $contact) {
                    $contact['uuid'] = Str::uuid();
                    $contr_agent_model->contacts()->create($contact);
                }
            }

            foreach ($provider_contracts as $provider_contract) {
                $provider_contract['uuid'] = Str::uuid();
                ProviderContractDocument::query()->create($provider_contract);
            }

            foreach ($nomenclatures as $nomenclature) {
                $nomenclature_model = Nomenclature::query()->updateOrCreate([
                    'mnemocode' => $nomenclature['mnemocode'],
                    'name' => $nomenclature['name'],
                    'uuid' => Str::uuid(),
                    'price' => $nomenclature['price'],
                ]);

                foreach ($nomenclature['units'] as $unit) {
                    $unit = NomenclatureUnit::query()->firstOrCreate([
                        'uuid' => Str::uuid(),
                        'name' => $unit,
                    ]);
                    $nomenclature_model->units()->attach($unit->id);
                }
            }
        });

        $this->call([
            ConsignmentSeeder::class,
        ]);


        //create json structure for sync order for integration example
        $object = CustomerObject::query()->firstOrFail();
        $example = [
            "orders" => [
                [
                    "id" => "15e38984-9fdd-11ec-a20a-00155d9b77db",
                    "number" => "000000001",
                    "order_date" => "01.12.2022",
                    "deadline_date" => "02.03.2023",
                    "customer_status" => "Согласовано",
                    "provider_status" => "Не согласовано",
                    "order_customer" => [
                        "organization_id" => Organization::query()->firstOrFail()->uuid,
                        "work_agreement_id" => WorkAgreementDocument::query()->firstOrFail()->uuid,
                        "work_type" => "Строительство",
                        "object_id" => $object->uuid,
                        "sub_object_id" => $object->subObjects()->firstOrFail()->uuid,
                        "work_start_date" => "01.12.2022",
                        "work_end_date" => "20.10.2025"
                    ],
                    "order_provider" => [
                        "contr_agent_id" => ContrAgent::query()->findOrFail(1)->uuid,
                        "provider_contract_id" => ProviderContractDocument::query()->findOrFail(1)->uuid,
                        "full_name" => "Full Name",
                        "email" => "fddsd@mail.ru",
                        "phone" => "+54566456546"
                    ],
                    "order_contractor" => [
                        "contr_agent_id" => ContrAgent::query()->findOrFail(1)->uuid,
                        "full_name" => "Name Name",
                        "email" => "fsdf@mail.ru",
                        "phone" => "+95947374834",
                        "contractor_responsible_full_name" => "Ответственный О.О.",
                        "contractor_responsible_phone" => "89172223126"
                    ],
                    "order_positions" => [
                        [
                            "position_id" => "6b92a3f6-c551-4ab2-9e7b-e36a683194f9",
                            "status" => "Согласовано",
                            "nomenclature_id" => Nomenclature::query()->findOrFail(1)->uuid,
                            "count" => 100,
                            "price_without_vat" => 200,
                            "amount_without_vat" => 20000,
                            "delivery_time" => "01.10.2034",
                            "delivery_address" => "address"
                        ],
                        [
                            "position_id" => "6b92a3f6-c351-4ab2-9e7b-e36a683194f9",
                            "status" => "Согласовано",
                            "nomenclature_id" => Nomenclature::query()->findOrFail(2)->uuid,
                            "count" => 300,
                            "price_without_vat" => 300,
                            "amount_without_vat" => 90000,
                            "delivery_time" => "04.04.2022",
                            "delivery_address" => "address"
                        ],
                        [
                            "position_id" => "6b92a3f6-c351-4ab2-9e7b-e36a683194f9",
                            "status" => "Согласовано",
                            "nomenclature_id" => Nomenclature::query()->findOrFail(3)->uuid,
                            "count" => 300,
                            "price_without_vat" => 300,
                            "amount_without_vat" => 90000,
                            "delivery_time" => "04.04.2022",
                            "delivery_address" => "address"
                        ],
                    ]
                ]
            ]
        ];
        Log::debug('example_order_request_for_sync', $example);

        $this->call(ProviderOrderSeeder::class);

    }
}
