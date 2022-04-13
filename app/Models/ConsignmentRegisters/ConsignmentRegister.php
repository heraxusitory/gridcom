<?php

namespace App\Models\ConsignmentRegisters;

use App\Traits\UsesConsignmentRegisterNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentRegister extends Model
{
    use HasFactory, UsesConsignmentRegisterNumber;


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

    const ACTION_DRAFT = 'draft';
    const ACTION_APPROVE = 'approve';

    const CUSTOMER_STATUS_NOT_AGREED = 'Не согласовано';
    const CUSTOMER_STATUS_AGREED = 'Согласовано';
    const CUSTOMER_STATUS_DRAFT = 'Черновик';
    const CUSTOMER_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const CUSTOMER_STATUS_CANCELED = 'Аннулировано';

    const PROVIDER_STATUS_NOT_AGREED = 'Не согласовано';
    const PROVIDER_STATUS_AGREED = 'Согласовано';
    const PROVIDER_STATUS_DRAFT = 'Черновик';
    const PROVIDER_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const PROVIDER_STATUS_CANCELED = 'Аннулировано';

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

    public static function getProviderStatuses(): array
    {
        return [
            self::PROVIDER_STATUS_AGREED,
            self::PROVIDER_STATUS_DRAFT,
            self::PROVIDER_STATUS_UNDER_CONSIDERATION,
            self::PROVIDER_STATUS_NOT_AGREED,
            self::PROVIDER_STATUS_CANCELED
        ];
    }

    public static function getContrAgentStatuses(): array
    {
        return [
            self::PROVIDER_STATUS_AGREED,
            self::PROVIDER_STATUS_DRAFT,
            self::PROVIDER_STATUS_UNDER_CONSIDERATION,
            self::PROVIDER_STATUS_NOT_AGREED,
            self::PROVIDER_STATUS_CANCELED,
            self::CONTRACTOR_STATUS_SELF_PURCHASE,
        ];
    }

    public function positions()
    {
        return $this->hasMany(
            ConsignmentRegisterPositions::class,
            'consignment_register_id', 'id');
    }
}
