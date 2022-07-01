<?php


namespace App\Services\RequestAdditionNomenclatures;


use App\Events\NewStack;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CreateRequestAdditionNomenclatureService implements IService
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct(private $payload)
    {
        $this->user = Auth::user();
    }

    public function run()
    {
        $data = $this->payload;

        $organization_status = match ($data['action']) {
            RequestAdditionNomenclature::ACTION_DRAFT() => RequestAdditionNomenclature::ORGANIZATION_STATUS_DRAFT,
            RequestAdditionNomenclature::ACTION_APPROVE() => RequestAdditionNomenclature::ORGANIZATION_STATUS_UNDER_CONSIDERATION,
            default => throw new BadRequestException('Action is required', 400),
        };

        return DB::transaction(function () use ($data, $organization_status) {
            /** @var RequestAdditionNomenclature $ra_nomenclature */
            $ra_nomenclature = RequestAdditionNomenclature::query()->create([
                'uuid' => Str::uuid(),
                'date' => Carbon::today()->format('Y-m-d'),
                'file_url' => $file_link ?? null,
                'contr_agent_id' => $this->user->contr_agent_id(),
                'work_agreement_id' => $data['work_agreement_id'] ?? null,
                'provider_contract_id' => $data['provider_contract_id'] ?? null,
                'organization_id' => $data['organization_id'],
                'organization_status' => $organization_status,
                'type' => $data['type'],
                'nomenclature_id' => $data['type'] === RequestAdditionNomenclature::TYPE_CHANGE() ? $data['nomenclature_id'] : null,
                'nomenclature_name' => $data['type'] === RequestAdditionNomenclature::TYPE_NEW() ? $data['nomenclature_name'] : null,
                'nomenclature_unit' => $data['type'] === RequestAdditionNomenclature::TYPE_NEW() ? $data['nomenclature_unit'] : null,
                'description' => $data['description'],
                'responsible_full_name' => $data['responsible_full_name'],
                'contr_agent_comment' => $data['contr_agent_comment'],
                'organization_comment' => $data['organization_comment'] ?? null,
            ]);


            if (isset($data['file'])) {
                $file_link = Storage::putFile('request-addition-nomenclature/' . $ra_nomenclature->id, $data['file']);
                $ra_nomenclature->file_url = /*Storage::disk('public')->url(*/
                    $file_link/*)*/
                ;
                $ra_nomenclature->save();
            }

            if ($ra_nomenclature->organization_status !== RequestAdditionNomenclature::ORGANIZATION_STATUS_DRAFT) {
                if ($this->user->isProvider())
                    event(new NewStack($ra_nomenclature,
                            (new ProviderSyncStack())->setProvider($this->user->contr_agent()))
                    );
                if ($this->user->isContractor())
                    event(new NewStack($ra_nomenclature,
                            (new ContractorSyncStack())->setContractor($this->user->contr_agent()))
                    );

                event(new NewStack($ra_nomenclature,
                        new MTOSyncStack())
                );
            }

            return $ra_nomenclature;
        });
    }
}
