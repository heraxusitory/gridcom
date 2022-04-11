<?php


namespace App\Services\RequestAdditionNomenclatures;


use App\Models\RequestAdditions\RequestAdditionNomenclature;
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
            $ra_nomenclature = RequestAdditionNomenclature::query()->create([
                'uuid' => Str::uuid(),
                'date' => Carbon::today()->format('d.m.Y'),
                'file_url' => $file_link ?? null,
                'contr_agent_id' => $this->user->contr_agent_id(),
                'work_agreement_id' => $data['work_agreement_id'] ?? null,
                'provider_contract_id' => $data['provider_contract_id'] ?? null,
                'organization_id' => $data['organization_id'],
                'organization_status' => $organization_status,
                'nomenclature_id' => $data['nomenclature_id'],
                'description' => $data['description'],
                'responsible_full_name' => $data['responsible_full_name'],
                'contr_agent_comment' => $data['contr_agent_comment'],
            ]);


            if (isset($data['file'])) {
                $file_link = Storage::disk('public')->putFile('request-addition-nomenclature/' . $ra_nomenclature->id, $data['file']);
                $ra_nomenclature->file_url = Storage::disk('public')->url($file_link);
                $ra_nomenclature->save();
            }
            return $ra_nomenclature;
        });
    }
}
