<?php


namespace App\Models\Notifications;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationNotification extends Notification
{
    use HasFactory;

    protected $table = 'organization_notifications';

    protected $fillable = [
        'uuid',
        'date',
        'status',
        'organization_id',
        'provider_contr_agent_id',
        'contract_stage',
        'contract_number',
        'contract_date',
        'date_fact_delivery',
        'delivery_address',
        'car_info',
        'driver_phone',
        'responsible_full_name',
        'responsible_phone',
        'organization_comment',
    ];

    const ORGANIZATION_STATUS_NOT_AGREED = 'Не согласовано';
    const ORGANIZATION_STATUS_AGREED = 'Согласовано';
    const ORGANIZATION_STATUS_DRAFT = 'Черновик';
    const ORGANIZATION_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const ORGANIZATION_STATUS_CANCELED = 'Аннулировано';

    public static function getOrganizationStatuses()
    {
        return [
            self::ORGANIZATION_STATUS_NOT_AGREED,
            self::ORGANIZATION_STATUS_AGREED,
            self::ORGANIZATION_STATUS_DRAFT,
            self::ORGANIZATION_STATUS_UNDER_CONSIDERATION,
            self::ORGANIZATION_STATUS_CANCELED
        ];
    }

    public function positions()
    {
        return $this->hasMany(OrganizationNotificationPosition::class, 'organization_notification_id', 'id');
    }

}
