<?php


namespace App\Services\API\ContrAgents\v1;


use App\Events\NewStack;
use App\Models\Consignments\Consignment;
use App\Models\IntegrationUser;
use App\Models\Orders\Order;
use App\Models\Provider;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateConsignmentService implements IService
{
    public function __construct(private $data, private IntegrationUser $user)
    {
    }

    public function run()
    {
        foreach ($this->data as $item) {
            DB::transaction(function () use ($item) {
                $position_data = $item['positions'] ?? [];

                $provider_contr_agent = ContrAgent::query()->where(['name' => $item['provider_contr_agent']['name']])->first();
                $contractor_contr_agent = ContrAgent::query()->where(['name' => $item['contractor_contr_agent']['name']])->first();
                if ($this->user->isContractor())
                    /** @var ContrAgent $provider_contr_agent */ $contractor_contr_agent = $this->user->contr_agent()->firstOrFail();
                if ($this->user->isProvider())
                    /** @var ContrAgent $provider_contr_agent */ $provider_contr_agent = $this->user->contr_agent()->firstOrFail();

                /** @var Consignment $consignment */
                $consignment = Consignment::withoutEvents(function () use ($contractor_contr_agent, $provider_contr_agent, $item) {
                    $organization = Organization::query()->where(['name' => $item['organization']['name']])->first();
                    $provider_contract = ProviderContractDocument::query()->where(['number' => $item['provider_contract']['number']])->first();
                    $work_agreement = WorkAgreementDocument::query()->where(['number' => $item['work_agreement']['number']])->first();
                    $object = CustomerObject::query()->where(['name' => $item['customer_object']['name']])->first();
                    $sub_object = $object?->subObjects()
                        ->where(['name' => $item['customer_sub_object']['name']])
                        ->first();

                    $consignment = collect([
                        'uuid' => $item['id'],
                        'number' => $item['number'],
                        'date' => $item['date'],
                        'organization_id' => $organization?->id,
                        'provider_contr_agent_id' => $provider_contr_agent?->id,
                        'provider_contract_id' => $provider_contract?->id,
                        'contractor_contr_agent_id' => $contractor_contr_agent?->id,
                        'work_agreement_id' => $work_agreement?->id,
                        'customer_object_id' => $object?->id,
                        'customer_sub_object_id' => $sub_object?->id,
                        'responsible_full_name' => $item['responsible_full_name'] ?? null,
                        'responsible_phone' => $item['responsible_phone'] ?? null,
                        'comment' => $item['comment'] ?? null,
                    ]);
                    return Consignment::query()->updateOrCreate([
                        'uuid' => $consignment['uuid'],
                    ], $consignment->toArray());
                });

                $position_ids = [];
                foreach ($position_data as $position) {
                    $nomenclature = Nomenclature::query()
                        ->where([
                            'mnemocode' => $position['nomenclature']['mnemocode'],
                        ])
                        ->orWhere('name', $position['nomenclature']['name'])
                        ->first();
                    $order = Order::query()->firstOrCreate([
                        'uuid' => $position['order_id']
                    ]);

                    $position = collect([
                        'position_id' => $position['id'],
                        'order_id' => $order->id,
                        'nomenclature_id' => $nomenclature?->id,
                        'count' => $position['count'],
                        'price_without_vat' => $position['price_without_vat'],
                        'amount_without_vat' => $position['amount_without_vat'],
                        'vat_rate' => $position['vat_rate'],
                        'amount_with_vat' => $position['amount_with_vat'],
                        'country' => $position['country'],
                        'cargo_custom_declaration' => $position['cargo_custom_declaration'] ?? null,
                        'declaration' => $position['declaration'] ?? null,
                    ]);
                    $position = $consignment->positions()->updateOrCreate([
                        'position_id' => $position['position_id']
                    ], $position->toArray());
                    $position_ids[] = $position->id;
                }
                $consignment->positions()->whereNotIn('id', $position_ids)->delete();

                if ($this->user->isContractor())
                    event(new NewStack($consignment,
                            (new ProviderSyncStack())->setProvider($provider_contr_agent),
                            (new MTOSyncStack()))
                    );
                if ($this->user->isProvider())
                    event(new NewStack($consignment,
                        (new ContractorSyncStack())->setContractor($contractor_contr_agent)),
                        (new MTOSyncStack())
                    );
            });
        }
    }
}
