<?php


namespace App\Models;


use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\Consignments\Consignment;
use App\Models\Notifications\OrganizationNotification;
use App\Models\Orders\Order;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\ProviderOrders\Corrections\RequirementCorrection;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\RequestAdditions\RequestAdditionObject;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'notificationable_id',
        'notificationable_type',
        'body',
        'header',
        'config_data',
    ];

    protected $casts = [
        'config_data' => 'array',
    ];

    private const ENTITY_ORDERS = 'orders';
    private const ENTITY_CONSIGNMENTS = 'consignments';
    private const ENTITY_CONSIGNMENT_REGISTERS = 'consignment_registers';
    private const ENTITY_PAYMENT_REGISTERS = 'payment_registers';
    private const ENTITY_PROVIDER_ORDERS = 'provider_orders';
    private const ENTITY_RA_NOMENCLATURE = 'request_addition_nomenclature';
    private const ENTITY_RA_OBJECTS = 'request_addition_objects';
    private const ENTITY_ORGANIZATION_NOTIFICATIONS = 'organization_notifications';
    private const ENTITY_REQUIREMENT_CORRECTIONS = 'requirement_corrections';

    public static function ENTITIES(): array
    {
        return [
            self::ENTITY_ORDERS,
            self::ENTITY_CONSIGNMENTS,
            self::ENTITY_CONSIGNMENT_REGISTERS,
            self::ENTITY_PAYMENT_REGISTERS,
            self::ENTITY_PROVIDER_ORDERS,
            self::ENTITY_RA_NOMENCLATURE,
            self::ENTITY_RA_OBJECTS,
            self::ENTITY_ORGANIZATION_NOTIFICATIONS,
            self::ENTITY_REQUIREMENT_CORRECTIONS,
        ];
    }

    public static function ENTITY_TO_MODEL(): array
    {
        return [
            self::ENTITY_ORDERS => Order::class,
            self::ENTITY_CONSIGNMENTS => Consignment::class,
            self::ENTITY_CONSIGNMENT_REGISTERS => ConsignmentRegister::class,
            self::ENTITY_PAYMENT_REGISTERS => PaymentRegister::class,
            self::ENTITY_PROVIDER_ORDERS => ProviderOrder::class,
            self::ENTITY_RA_NOMENCLATURE => RequestAdditionNomenclature::class,
            self::ENTITY_RA_OBJECTS => RequestAdditionObject::class,
            self::ENTITY_ORGANIZATION_NOTIFICATIONS => OrganizationNotification::class,
            self::ENTITY_REQUIREMENT_CORRECTIONS => RequirementCorrection::class,
        ];
    }


    public function notificationable()
    {
        return $this->morphTo();
    }
}
