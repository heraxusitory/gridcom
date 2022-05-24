<?php


namespace App\Models\PaymentRegisters;


use App\Interfaces\Syncable;
use App\Models\Orders\Order;
use App\Models\References\ContrAgent;
use App\Models\References\ProviderContractDocument;
use App\Traits\UseNotification;
use App\Traits\UsesNumberLKK;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PaymentRegister extends Model implements Syncable
{
    use UsesNumberLKK, UseNotification;

    protected $table = 'payment_registers';

    protected $fillable = [
        'uuid',
        'number',
        'customer_status',
        'provider_status',
        'provider_contr_agent_id',
        'contractor_contr_agent_id',
        'provider_contract_id',
        'responsible_full_name',
        'responsible_phone',
        'comment',
        'date',
    ];

    protected $with = [
        'provider', 'contractor', 'provider_contract',
        'positions.order.customer.object',
        'positions.order.customer.organization',
        'positions.order.customer.contract',
    ];

    public function positions()
    {
        return $this->hasMany(PaymentRegisterPosition::class, 'payment_register_id', 'id');
    }

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

    public static function getActions()
    {
        return [
            self::ACTION_APPROVE,
            self::ACTION_DRAFT,
        ];
    }

    public static function getCustomerStatuses(): array
    {
        return [
            self::CUSTOMER_STATUS_NOT_AGREED,
            self::CUSTOMER_STATUS_AGREED,
            self::CUSTOMER_STATUS_DRAFT,
            self::CUSTOMER_STATUS_UNDER_CONSIDERATION,
            self::CUSTOMER_STATUS_CANCELED,
        ];
    }

    public static function getProviderStatuses(): array
    {
        return [
            self::PROVIDER_STATUS_NOT_AGREED,
            self::PROVIDER_STATUS_AGREED,
            self::PROVIDER_STATUS_DRAFT,
            self::PROVIDER_STATUS_UNDER_CONSIDERATION,
            self::PROVIDER_STATUS_CANCELED,
        ];
    }

    public function getDateAttribute($value)
    {
        return (new Carbon($value))->format('Y-m-d');
    }

    public function provider()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'provider_contr_agent_id');
    }

    public function contractor()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'contractor_contr_agent_id');
    }

    public function provider_contract()
    {
        return $this->hasOne(ProviderContractDocument::class, 'id', 'provider_contract_id');
    }
}
