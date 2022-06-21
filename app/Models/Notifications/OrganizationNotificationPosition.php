<?php


namespace App\Models\Notifications;


use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\Nomenclature;
use App\Traits\Filterable;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;

class OrganizationNotificationPosition extends Model
{
    use Filterable, Sortable;

    protected $table = 'organization_notification_positions';

    protected $fillable = [
        'position_id',
        'order_id',
//        'provider_order_id',
        'price_without_vat',
        'nomenclature_id',
        'count',
        'vat_rate',
    ];

    protected $casts = [
        'price_without_vat' => 'float',
        'count' => 'float',
        'nomenclature_id' => 'integer',
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
