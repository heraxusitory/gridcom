<?php


namespace App\Models\Notifications;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorNotification extends Notification
{
    use HasFactory;

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

    public function positions()
    {
        return $this->hasMany(ContractorNotificationPosition::class, 'contractor_notification_id', 'id');
    }

}
