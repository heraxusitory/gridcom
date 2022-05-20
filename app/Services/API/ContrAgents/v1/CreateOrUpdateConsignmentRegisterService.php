<?php


namespace App\Services\API\ContrAgents\v1;


use App\Events\NewStack;
use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\Consignments\Consignment;
use App\Models\IntegrationUser;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\WorkAgreementDocument;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateConsignmentRegisterService implements IService
{
    public function __construct(private $data, private IntegrationUser $user)
    {
    }

    public function run()
    {
        foreach ($this->data as $item) {
            DB::transaction(function () use ($item) {
                $position_data = $item['positions'] ?? [];

                /** @var ContrAgent $contractor_contr_agent */
                $contractor_contr_agent = $this->user->isContractor() ? $this->user->contr_agent()->firstOrFail() : ContrAgent::query()->where([
                    'name' => $item['contractor_contr_agent']['name'],
                ])->first();
                /** @var ContrAgent $provider_contr_agent */
                $provider_contr_agent = $this->user->isProvider() ? $this->user->contr_agent()->firstOrFail() : ContrAgent::query()->where([
                    'name' => $item['provider_contr_agent']['name'],
                ])->first();

                /** @var ConsignmentRegister $consignment_register */
                $consignment_register = ConsignmentRegister::withoutEvents(function () use ($provider_contr_agent, $contractor_contr_agent, $item) {
                    $organization = Organization::query()->where([
                        'name' => $item['organization']['name'],
                    ])->first();

                    $customer_object = CustomerObject::query()->where([
                        'name' => $item['customer_object']['name'],
                    ])->first();
                    $customer_sub_object = !empty($item['customer_sub_object']['name']) ? $customer_object?->subObjects()
                        ->where(['name' => $item['customer_sub_object']['name']])
                        ->first() : null;

                    $work_agreement = WorkAgreementDocument::query()->where([
                        'number' => $item['work_agreement']['number'],
                    ])->first();

                    $cr_data = collect([
                        'uuid' => $item['id'],
                        'number' => $item['number'],
                        'customer_status' => $item['customer_status'],
                        'contr_agent_status' => $item['contr_agent_status'],
                        'organization_id' => $organization?->id,
                        'contractor_contr_agent_id' => $contractor_contr_agent->id,
                        'provider_contr_agent_id' => $provider_contr_agent->id,
                        'customer_object_id' => $customer_object?->id,
                        'customer_sub_object_id' => $customer_sub_object?->id,
                        'work_agreement_id' => $work_agreement?->id,
                        'responsible_full_name' => $item['responsible_full_name'],
                        'responsible_phone' => $item['responsible_phone'],
                        'comment' => $item['comment'],
                        'date' => $item['date'],
                    ]);

                    return ConsignmentRegister::query()->updateOrCreate([
                        'uuid' => $cr_data['uuid'],
                    ], $cr_data->toArray());
                });

                $position_ids = [];
                foreach ($position_data as $position) {
                    $consignment = Consignment::withoutEvents(function () use ($position) {
                        return Consignment::query()->firstOrCreate([
                            'uuid' => $position['consignment_id']
                        ]);
                    });
                    $nomenclature = Nomenclature::query()->where([
                        'name' => $position['nomenclature']['name'],
                        'mnemocode' => $position['nomenclature']['mnemocode'],
                    ])->first();

                    $cr_position_data = collect([
                        'position_id' => $position['id'],
                        'consignment_id' => $consignment->id,
                        'nomenclature_id' => $nomenclature?->id,
                        'price_without_vat' => $position['price_without_vat'],
                        'amount_without_vat' => $position['amount_without_vat'],
                        'count' => $position['count'],
                        'vat_rate' => $position['vat_rate'],
                        'amount_with_vat' => $position['amount_with_vat'],
                        'result_status' => $position['result_status'],
                    ]);
                    $position = $consignment_register->positions()->updateOrCreate([
                        'position_id' => $cr_position_data['position_id'],
                    ], $cr_position_data->toArray());
                    $position_ids[] = $position->id;
                }
                $consignment_register->positions()->whereNotIn('id', $position_ids)->delete();

                if ($this->user->isContractor())
                    event(new NewStack($consignment_register,
                            (new ProviderSyncStack())->setProvider($provider_contr_agent),
                            new MTOSyncStack())
                    );
                if ($this->user->isProvider())
                    event(new NewStack($consignment_register,
                            (new ContractorSyncStack())->setContractor($contractor_contr_agent),
                            new MTOSyncStack())
                    );
            });
        }
    }

}
