<?php


namespace App\Models\Notifications;


use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\Nomenclature;
use Illuminate\Database\Eloquent\Model;

class OrganizationNotificationPosition extends Model
{
    protected $table = 'organization_notification_positions';

    protected $fillable = [
        'position_id',
        'order_id',
//        'provider_order_id',
        'nomenclature_id',
        'count',
        'vat_rate',
    ];

    public function order()
    {
        return $this->hasOne(ProviderOrder::class, 'id', 'order_id');
    }

    public function nomenclature()
    {
        return $this->hasOne(Nomenclature::class, 'id', 'nomenclature_id');
    }
}
