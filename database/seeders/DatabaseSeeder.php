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
    }
}
