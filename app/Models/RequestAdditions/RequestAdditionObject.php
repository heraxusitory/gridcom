<?php

namespace App\Models\RequestAdditions;

use App\Models\References\CustomerObject;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Traits\UsesNumberLKK;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAdditionObject extends Model
{
    use HasFactory, UsesNumberLKK;

    protected $table = 'request_addition_objects';

    protected $fillable = [
        'uuid',
        'number',
        'date',
        'contr_agent_id',
        'work_agreement_id',
        'provider_contract_id',
        'organization_id',
        'organization_status',
        'object_id',
        'description',
        'responsible_full_name',
        'contr_agent_comment',
        'organization_comment',
        'file_url',
    ];

    private const ACTION_DRAFT = 'draft';
    private const ACTION_APPROVE = 'approve';

    const ORGANIZATION_STATUS_NOT_AGREED = 'Не согласовано';
    const ORGANIZATION_STATUS_AGREED = 'Согласовано';
    const ORGANIZATION_STATUS_DRAFT = 'Черновик';
    const ORGANIZATION_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const ORGANIZATION_STATUS_CANCELED = 'Аннулировано';

    public static function getActions(): array
    {
        return [
            self::ACTION_APPROVE,
            self::ACTION_DRAFT,
        ];
    }

    public static function ACTION_APPROVE()
    {
        return self::ACTION_APPROVE;
    }

    public static function ACTION_DRAFT()
    {
        return self::ACTION_DRAFT;
    }

    public function work_agreement()
    {
        return $this->hasOne(WorkAgreementDocument::class, 'id', 'work_agreement_id');
    }

    public function provider_contract()
    {
        return $this->hasOne(ProviderContractDocument::class, 'id', 'provider_contract_id');
    }

    public function organization()
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function object()
    {
        return $this->hasOne(CustomerObject::class, 'id', 'object_id');
    }
}