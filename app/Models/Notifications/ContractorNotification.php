<?php


namespace App\Models\Notifications;


use App\Traits\UseNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ContractorNotification extends Notification
{
    use HasFactory, UseNotification;

    protected $table = 'contractor_notifications';

    protected $fillable = [
        'uuid',
        'date',
        'status',
        'contractor_contr_agent_id',
        'provider_contr_agent_id',
        'provider_contract_id',
        'date_fact_delivery',
        'delivery_address',
        'car_info',
        'driver_phone',
        'responsible_full_name',
        'responsible_phone',
        'contractor_comment',
    ];

    const CONTRACTOR_STATUS_NOT_AGREED = 'Не согласовано';
    const CONTRACTOR_STATUS_AGREED = 'Согласовано';
    const CONTRACTOR_STATUS_DRAFT = 'Черновик';
    const CONTRACTOR_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const CONTRACTOR_STATUS_CANCELED = 'Аннулировано';

    public static function getContractorStatuses()
    {
        return [
            self::CONTRACTOR_STATUS_DRAFT,
            self::CONTRACTOR_STATUS_UNDER_CONSIDERATION,
            self::CONTRACTOR_STATUS_AGREED,
            self::CONTRACTOR_STATUS_CANCELED,
            self::CONTRACTOR_STATUS_NOT_AGREED
        ];
    }

    public function positions()
    {
        return $this->hasMany(ContractorNotificationPosition::class, 'contractor_notification_id', 'id');
    }

    public function getDateAttribute($value)
    {
        return (new Carbon($value))->format('Y-m-d');
    }

    public function getDateFactDeliveryAttribute($value)
    {
        return (new Carbon($value))->format('Y-m-d');
    }

}
