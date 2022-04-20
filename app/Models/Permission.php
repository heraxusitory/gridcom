<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_permissions');
    }

    public const ACTION_CREATE = 'create';
    public const ACTION_VIEW = 'view';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';

    public const RESOURCE_ORDERS = 'orders';
    public const RESOURCE_PROVIDER_ORDERS = 'provider_orders';
    public const RESOURCE_PAYMENT_REGISTERS = 'payment_registers';
    public const RESOURCE_PRICE_NEGOTIATIONS = 'price_negotiations';
    public const RESOURCE_CONTRACTOR_NOTIFICATIONS = 'contractor_notifications';
    public const RESOURCE_ORGANIZATION_NOTIFICATIONS = 'organization_notifications';
    public const RESOURCE_CONSIGNMENTS = 'consignments';
    public const RESOURCE_CONSIGNMENT_REGISTERS = 'consignment_registers';
//    public const RESOURCE_REFERENCES = 'references';
    public const RESOURCE_REFERENCE_CONTR_AGENTS = 'contr_agents';
    public const RESOURCE_REFERENCE_OBJECTS = 'objects';
    public const RESOURCE_REFERENCE_ORGANIZATIONS = 'organizations';
    public const RESOURCE_REFERENCE_PROVIDER_CONTRACTS = 'provider_contracts';
    public const RESOURCE_REFERENCE_WORK_AGREEMENTS = 'work_agreements';
    public const RESOURCE_REFERENCE_NOMENCLATURE = 'nomenclature';

    public const RESOURCE_REQUEST_ADDITION_NOMENCLATURES = 'request_addition_nomenclatures';
    public const RESOURCE_REQUEST_ADDITION_OBJECTS = 'request_addition_objects';

    public static function ACTIONS()
    {
        return [
            self::ACTION_CREATE,
            self::ACTION_VIEW,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
        ];
    }

    public static function RESOURCES()
    {
        return [
            self::RESOURCE_ORDERS,
            self::RESOURCE_PROVIDER_ORDERS,
            self::RESOURCE_PAYMENT_REGISTERS,
            self::RESOURCE_PRICE_NEGOTIATIONS,
            self::RESOURCE_CONTRACTOR_NOTIFICATIONS,
            self::RESOURCE_ORGANIZATION_NOTIFICATIONS,
            self::RESOURCE_CONSIGNMENTS,
            self::RESOURCE_CONSIGNMENT_REGISTERS,
            self::RESOURCE_REQUEST_ADDITION_NOMENCLATURES,
            self::RESOURCE_REQUEST_ADDITION_OBJECTS,
            self::RESOURCE_REFERENCE_WORK_AGREEMENTS,
            self::RESOURCE_REFERENCE_PROVIDER_CONTRACTS,
            self::RESOURCE_REFERENCE_ORGANIZATIONS,
            self::RESOURCE_REFERENCE_OBJECTS,
            self::RESOURCE_REFERENCE_CONTR_AGENTS,
            self::RESOURCE_REFERENCE_NOMENCLATURE,
        ];
    }

    public static function buildPermissionName(array $items = [])
    {
        $substring = implode('.', $items);

        return $substring ?? '';
    }
}
