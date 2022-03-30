<?php


namespace App\Models\Notifications;


use Illuminate\Database\Eloquent\Model;

class ContractorNotificationPosition extends Model
{
    protected $table = 'contractor_notification_positions';

    protected $fillable = [
        'position_id',
        'order_id',
        'nomenclature_id',
        'count',
        'vat_rate',
    ];
}
