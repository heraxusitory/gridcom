<?php


namespace App\Models\Notifications;


use Illuminate\Database\Eloquent\Model;

class OrganizationNotificationPosition extends Model
{
    protected $table = 'organization_notification_positions';

    protected $fillable = [
        'provider_order_id',
        'mnemocode_id',
        'count',
        'vat_rate',
    ];
}
