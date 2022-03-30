<?php


namespace App\Models\Notifications;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationNotification extends Notification
{
    use HasFactory;

    protected $table = 'contractor_notifications';

    protected $fillable = [
        'date',
        'status',
        'organization_id',
        'provider_contr_agent_id',
        'stage',
        'work_agreement_id',
        'date_fact_delivery',
        'delivery_address',
        'car_info',
        'driver_phone',
        'responsible_full_name',
        'responsible_phone',
        'comment',
    ];

    public function positions()
    {
        return $this->hasMany(OrganizationNotificationPosition::class, 'notification_id', 'id');
    }

}
