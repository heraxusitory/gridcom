<?php


namespace App\Services\RequestAdditionObjects;


use App\Models\RequestAdditions\RequestAdditionObject;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateRequestAdditionObjectService implements IService
{
    public function __construct(private $payload, private RequestAdditionObject $ra_object)
    {
    }

    public function run()
    {
        $data = $this->payload;

        $organization_status = match ($data['action']) {
            RequestAdditionObject::ACTION_DRAFT() => RequestAdditionObject::ORGANIZATION_STATUS_DRAFT,
            RequestAdditionObject::ACTION_APPROVE() => RequestAdditionObject::ORGANIZATION_STATUS_UNDER_CONSIDERATION,
            default => throw new BadRequestException('Action is required', 400),
        };

        return DB::transaction(function () use ($data, $organization_status) {
            $this->ra_object->update(
                [
                    'date' => Carbon::today()->format('d.m.Y'),
                    'file_url' => $file_link ?? null,
                    'work_agreement_id' => $data['work_agreement_id'] ?? null,
                    'provider_contract_id' => $data['provider_contract_id'] ?? null,
                    'organization_id' => $data['organization_id'],
                    'organization_status' => $organization_status,
                    'object_id' => $data['object_id'],
                    'description' => $data['description'],
                    'responsible_full_name' => $data['responsible_full_name'],
                    'contr_agent_comment' => $data['contr_agent_comment'],
                ]);


            $old_file_url = $this->ra_object->file_url;
            if (!is_null($old_file_url)) {
                Storage::disk('public')->delete($old_file_url);
            }
            $this->ra_object->file_url = null;

            if (isset($data['file'])) {
                $file_link = Storage::disk('public')->putFile('request-addition-objects' . $this->ra_object->id, $data['file']);
                $this->ra_object->file_url = $file_link;
            }
            $this->ra_object->save();

            return $this->ra_object;
        });
    }
}
