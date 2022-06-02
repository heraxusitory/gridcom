<?php


namespace App\Services\API\ContrAgents\v1;


use App\Events\NewStack;
use App\Models\IntegrationUser;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\SyncStacks\MTOSyncStack;
use App\Services\IService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateOrUpdateRANomenclatureService implements IService
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
                $nomenclature_query = Nomenclature::query()
                    ->where('name', $item['nomenclature']['name']);
                if ($item['nomenclature']['mnemocode'] ?? null) {
                    $nomenclature_query->orWhere('mnemocode', $item['nomenclature']['mnemocode']);
                }
                $nomenclature = $nomenclature_query->first();

                $ra_nomenclature_data = collect([
                    'uuid' => $item['id'],
                    'type' => $item['type'],
                    'number' => $item['number'] ?? null,
                    'date' => $item['date'] ?? null,
                    'contr_agent_id' => $this->user->contr_agent->id,
                    'work_agreement_id' => $this->user->isContractor() ? $work_agreement?->id : null,
                    'provider_contract_id' => $this->user->isProvider() ? $provider_contract?->id : null,
                    'organization_id' => $organization?->id,
                    'nomenclature_id' => $item['type'] === RequestAdditionNomenclature::TYPE_CHANGE() ? $nomenclature?->id : null,
                    'nomenclature_name' => $item['type'] === RequestAdditionNomenclature::TYPE_NEW() ? $item['nomenclature']['mnemocode'] . ' ' . $item['nomenclature']['name'] : null,
                    'description' => $item['description'] ?? null,
                    'responsible_full_name' => $item['responsible_full_name'] ?? null,
                    'contr_agent_comment' => $item['contr_agent_comment'] ?? null,
                ]);

                $ra_nomenclature = RequestAdditionNomenclature::query()->where('uuid', $ra_nomenclature_data['uuid'])->first();
                if (!is_null($ra_nomenclature))
                    $ra_nomenclature->update($ra_nomenclature_data->toArray());
                else $ra_nomenclature = RequestAdditionNomenclature::query()->create(array_merge($ra_nomenclature_data->toArray(), ['organization_status' => RequestAdditionNomenclature::ORGANIZATION_STATUS_UNDER_CONSIDERATION]));

                $old_file_url = $ra_nomenclature->file_url;
                if (!is_null($old_file_url)) {
                    Storage::disk('public')->delete($old_file_url);
                }
                $ra_nomenclature->file_url = null;

                if (isset($item['file'])) {
                    $file_link = Storage::disk('public')->putFile('request-addition-nomenclature/' . $ra_nomenclature->id, $item['file']);
                    $ra_nomenclature->file_url = $file_link;
                }
                $ra_nomenclature->save();

                event(new NewStack($ra_nomenclature, (new MTOSyncStack())));
            });
        }
    }
}
