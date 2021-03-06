<?php

namespace App\Models\ConsignmentRegisters;

use App\Interfaces\Syncable;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Organization;
use App\Models\References\WorkAgreementDocument;
use App\Traits\Filterable;
use App\Traits\Sortable;
use App\Traits\UseNotification;
use App\Traits\UsesNumberLKK;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ConsignmentRegister extends Model implements Syncable
{
    use HasFactory, UsesNumberLKK, UseNotification, Filterable, Sortable;


    protected $table = 'consignment_registers';

    protected $fillable = [
        'uuid',
        'number',
        'customer_status',
        'contr_agent_status',

        'organization_id',
        'contractor_contr_agent_id',
        'provider_contr_agent_id',
        'customer_object_id',
        'customer_sub_object_id',
        'work_agreement_id',
        'responsible_full_name',
        'responsible_phone',
        'comment',
        'date',
    ];

    protected $with = [
        'organization', 'contractor', 'provider',
        'object', 'subObject', 'work_agreement',
        'positions.consignment', 'positions.nomenclature',
    ];

    const ACTION_DRAFT = 'draft';
    const ACTION_APPROVE = 'approve';

    const CUSTOMER_STATUS_NOT_AGREED = 'Не согласовано';
    const CUSTOMER_STATUS_AGREED = 'Согласовано';
    const CUSTOMER_STATUS_DRAFT = 'Черновик';
    const CUSTOMER_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const CUSTOMER_STATUS_CANCELED = 'Аннулировано';

//    const PROVIDER_STATUS_NOT_AGREED = 'Не согласовано';
//    const PROVIDER_STATUS_AGREED = 'Согласовано';
//    const PROVIDER_STATUS_DRAFT = 'Черновик';
//    const PROVIDER_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
//    const PROVIDER_STATUS_CANCELED = 'Аннулировано';

    const CONTRACTOR_STATUS_NOT_AGREED = 'Не согласовано';
    const CONTRACTOR_STATUS_AGREED = 'Согласовано';
    const CONTRACTOR_STATUS_DRAFT = 'Черновик';
    const CONTRACTOR_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';

    const CONTRACTOR_STATUS_SELF_PURCHASE = 'Самозакуп';

    public static function getActions(): array
    {
        return [
            self::ACTION_APPROVE,
            self::ACTION_DRAFT,
        ];
    }

    public static function getCustomerStatuses(): array
    {
        return [
            self::CUSTOMER_STATUS_AGREED,
            self::CUSTOMER_STATUS_NOT_AGREED,
            self::CUSTOMER_STATUS_UNDER_CONSIDERATION,
            self::CUSTOMER_STATUS_UNDER_CONSIDERATION,
            self::CUSTOMER_STATUS_DRAFT,
            self::CUSTOMER_STATUS_CANCELED
        ];
    }

    public static function getContractorStatuses(): array
    {
        return [
            self::CONTRACTOR_STATUS_SELF_PURCHASE,
            self::CONTRACTOR_STATUS_UNDER_CONSIDERATION,
            self::CONTRACTOR_STATUS_DRAFT,
            self::CONTRACTOR_STATUS_AGREED,
            self::CONTRACTOR_STATUS_NOT_AGREED,
//            self::PROVIDER_STATUS_AGREED,
//            self::PROVIDER_STATUS_DRAFT,
//            self::PROVIDER_STATUS_UNDER_CONSIDERATION,
//            self::PROVIDER_STATUS_NOT_AGREED,
//            self::PROVIDER_STATUS_CANCELED
        ];
    }

    public static function getContrAgentStatuses(): array
    {
        return [
            self::CONTRACTOR_STATUS_AGREED,
            self::CONTRACTOR_STATUS_DRAFT,
            self::CONTRACTOR_STATUS_UNDER_CONSIDERATION,
            self::CONTRACTOR_STATUS_NOT_AGREED,
//            self::CONTRACTOR_STATUS_CANCELED,
            self::CONTRACTOR_STATUS_SELF_PURCHASE,
        ];
    }

    public function positions()
    {
        return $this->hasMany(
            ConsignmentRegisterPosition::class,
            'consignment_register_id', 'id');
    }

    public function organization()
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function getDateAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }

    public function provider()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'provider_contr_agent_id');
    }

    public function contractor()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'contractor_contr_agent_id');
    }


    public function object()
    {
        return $this->hasOne(CustomerObject::class, 'id', 'customer_object_id');
    }

    public function subObject()
    {
        return $this->hasOne(CustomerSubObject::class, 'id', 'customer_sub_object_id');
    }

    public function work_agreement()
    {
        return $this->hasOne(WorkAgreementDocument::class, 'id', 'work_agreement_id');
    }


}
