<?php


namespace App\Models\Notifications;


use App\Interfaces\Syncable;
use App\Models\References\ContrAgent;
use App\Models\References\Organization;
use App\Traits\Filterable;
use App\Traits\Sortable;
use App\Traits\UseNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OrganizationNotification extends Notification implements Syncable
{
    use HasFactory, UseNotification, Filterable, Sortable;

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

    protected $with = [
        'positions.order',
        'positions.nomenclature',
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

    public function getDateAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }

    public function getDateFactDeliveryAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }

    public function organization()
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function provider()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'provider_contr_agent_id');
    }

}
