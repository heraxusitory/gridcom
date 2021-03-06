<?php

namespace App\Models\PriceNegotiations;

use App\Interfaces\Syncable;
use App\Models\Orders\Order;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\ContrAgent;
use App\Traits\Filterable;
use App\Traits\Sortable;
use App\Traits\UseNotification;
use App\Traits\UsesNumberLKK;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PriceNegotiation extends Model implements Syncable
{
    use HasFactory, UsesNumberLKK, UseNotification, Filterable, Sortable;

    protected $table = 'price_negotiations';

    protected $fillable = [
        'uuid',
        'type',
        'number',
        'date',
        'organization_status',
        'creator_contr_agent_id',
//        'object_id',
//        'sub_object_id',
//        'provider_contr_agent_id',
//        'contractor_contr_agent_id',
//        'organization_id',
        'order_id',
        'responsible_full_name',
        'responsible_phone',
        'comment',
        'file_url'
    ];

    private const TYPE_CONTRACT_WORK = 'contract_work';
    private const TYPE_CONTRACT_HOME_METHOD = 'contract_home_method';

    private const HUMAN_READABLE_TYPE_CONTRACT_WORK = 'Подрядные работы';
    private const HUMAN_READABLE_TYPE_HOME_METHOD = 'Договора поставки по хоз. способу';

    const ORGANIZATION_STATUS_NOT_AGREED = 'Не согласовано';
    const ORGANIZATION_STATUS_AGREED = 'Согласовано';
    const ORGANIZATION_STATUS_DRAFT = 'Черновик';
    const ORGANIZATION_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const ORGANIZATION_STATUS_CANCELED = 'Аннулировано';

    private const ACTION_DRAFT = 'draft';
    private const ACTION_APPROVE = 'approve';

    public static function getOrganizationStatuses()
    {
        return [
            self::ORGANIZATION_STATUS_NOT_AGREED,
            self::ORGANIZATION_STATUS_AGREED,
            self::ORGANIZATION_STATUS_DRAFT,
            self::ORGANIZATION_STATUS_UNDER_CONSIDERATION,
            self::ORGANIZATION_STATUS_CANCELED,
        ];
    }


    public static function HUMAN_READABLE_TYPES()
    {
        return [
            self::TYPE_CONTRACT_WORK => self::HUMAN_READABLE_TYPE_CONTRACT_WORK,
            self::TYPE_CONTRACT_HOME_METHOD => self::HUMAN_READABLE_TYPE_HOME_METHOD,
        ];
    }

    public static function TYPE_CONTRACT_WORK()
    {
        return self::TYPE_CONTRACT_WORK;
    }

    public static function TYPE_CONTRACT_HOME_METHOD()
    {
        return self::TYPE_CONTRACT_HOME_METHOD;
    }

    public static function TYPES()
    {
        return [
            self::TYPE_CONTRACT_WORK,
            self::TYPE_CONTRACT_HOME_METHOD,
        ];
    }

    public static function getActions()
    {
        return [
            self::ACTION_DRAFT,
            self::ACTION_APPROVE,
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

    public static function ACTIONS()
    {
        return [
            self::ACTION_DRAFT(),
            self::ACTION_APPROVE()
        ];
    }

    public function positions()
    {
        return $this->hasMany(PriceNegotiationPosition::class, 'price_negotiation_id', 'id');
    }

    public function order()
    {
        $order_model = match ($this->type) {
            self::TYPE_CONTRACT_WORK => Order::class,
            self::TYPE_CONTRACT_HOME_METHOD => ProviderOrder::class,
        };
        return $this->hasOne($order_model, 'id', 'order_id');
    }

    public function getDateAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }

    public function contr_agent()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'creator_contr_agent_id');
    }
}
