<?php


namespace App\Models\Notifications;


use Illuminate\Database\Eloquent\Model;

class OrganizationNotificationPosition extends Model
{
    protected $table = 'organization_notification_positions';

    protected $fillable = [
        'position_id',
        'order_id',
        'provider_order_id',
        'nomenclature_id',
        'count',
        'vat_rate',
    ];
}
