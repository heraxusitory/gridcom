<?php


namespace App\Models\Consignments;


use App\Interfaces\Syncable;
use App\Models\Orders\Order;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Traits\Filterable;
use App\Traits\Sortable;
use App\Traits\UseNotification;
use App\Traits\UsesNumberLKK;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Consignment extends Model implements Syncable
{
    use HasFactory, UsesNumberLKK, UseNotification, Filterable, Sortable;

    protected $table = 'consignments';

    protected $fillable = [
        'uuid',
        'number',
        'is_approved',
        'date',
        'organization_id',
        'provider_contr_agent_id',
        'provider_contract_id',
        'contractor_contr_agent_id',
        'work_agreement_id',
        'customer_object_id',
        'customer_sub_object_id',
//        'order_id',
        'responsible_full_name',
        'responsible_phone',
        'comment',
    ];

    protected $with = [
        'organization', 'provider', 'provider_contract',
        'contractor', 'work_agreement', 'object', 'subObject',
        'positions.nomenclature'
    ];

    private const ACTION_DRAFT = 'draft';
    private const ACTION_APPROVE = 'approve';

    public static function getActions(): array
    {
        return [
            self::ACTION_APPROVE,
            self::ACTION_DRAFT,
        ];
    }

    public static function ACTION_APPROVE()
    {
        return self::ACTION_APPROVE;
    }

    public static function ACTION_DRAFT()
    {
        return self::ACTION_DRAFT;
    }

//    public function order(): hasOne
//    {
//        return $this->hasOne(Order::class, 'id', 'order_id');
//    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function organization()
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function provider()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'provider_contr_agent_id');
    }

    public function contractor()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'contractor_contr_agent_id');
    }

    public function work_agreement()
    {
        return $this->hasOne(WorkAgreementDocument::class, 'id', 'work_agreement_id');
    }

    public function provider_contract()
    {
        return $this->hasOne(ProviderContractDocument::class, 'id', 'provider_contract_id');
    }

    public function object()
    {
        return $this->hasOne(CustomerObject::class, 'id', 'customer_object_id');
    }

    public function subObject()
    {
        return $this->hasOne(CustomerSubObject::class, 'id', 'customer_sub_object_id');
    }

    public function positions()
    {
        return $this->hasMany(ConsignmentPosition::class, 'consignment_id', 'id');
    }

    public function getDateAttribute($value)
    {
        return (new Carbon($value))->format('Y-m-d');
    }
}
