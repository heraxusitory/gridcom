<?php


namespace App\Services\API\ContrAgents\v1;


use App\Events\NewStack;
use App\Models\Customer;
use App\Models\IntegrationUser;
use App\Models\References\CustomerObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Models\RequestAdditions\RequestAdditionObject;
use App\Models\SyncStacks\MTOSyncStack;
use App\Services\IService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateOrUpdateRAObjectService implements IService
{
    public function __construct(private $data, private IntegrationUser $user)
    {
    }

    public function run()
    {
        foreach ($this->data as $item) {
            DB::transaction(function () use ($item) {
                $work_agreement = WorkAgreementDocument::query()
                    ->where('number', $item['work_agreement']['number'])
                    ->first();
                $provider_contract = ProviderContractDocument::query()
                    ->where('number', $item['provider_contract']['number'])
                    ->first();
                $organization = Organization::query()
                    ->where('name', $item['organization']['name'])
                    ->first();
                $object = CustomerObject::query()
                    ->where('name', $item['object']['name'])
                    ->first();

                $ra_object_data = collect([
                    'uuid' => $item['id'],
                    'type' => $item['type'],
                    'number' => $item['number'] ?? null,
                    'date' => $item['date'] ?? null,
                    'contr_agent_id' => $this->user->contr_agent->id,
                    'work_agreement_id' => $this->user->isContractor() ? $work_agreement?->id : null,
                    'provider_contract_id' => $this->user->isProvider() ? $provider_contract?->id : null,
                    'organization_id' => $organization?->id,
                    'object_id' => $item['type'] === RequestAdditionObject::TYPE_CHANGE() ? $object?->id : null,
                    'object_name' => $item['type'] === RequestAdditionObject::TYPE_NEW() ? $item['object']['name'] : null,
                    'description' => $item['description'] ?? null,
                    'responsible_full_name' => $item['responsible_full_name'] ?? null,
                    'contr_agent_comment' => $item['contr_agent_comment'] ?? null,
                ]);

                $ra_object = RequestAdditionObject::query()->where('uuid', $ra_object_data['uuid'])->first();
                if (!is_null($ra_object))
                    $ra_object->update($ra_object_data->toArray());
                else $ra_object = RequestAdditionObject::query()->create(array_merge($ra_object_data->toArray(), ['organization_status' => RequestAdditionObject::ORGANIZATION_STATUS_UNDER_CONSIDERATION]));

                $old_file_url = $ra_object->file_url;
                if (!is_null($old_file_url)) {
                    Storage::delete($old_file_url);
                }
                $ra_object->file_url = null;

                if (isset($item['file'])) {
                    $file_link = Storage::putFile('request-addition-nomenclature/' . $ra_object->id, $item['file']);
                    $ra_object->file_url = $file_link;
                }
                $ra_object->save();

                event(new NewStack($ra_object, (new MTOSyncStack())));
            });
        }
    }
}
