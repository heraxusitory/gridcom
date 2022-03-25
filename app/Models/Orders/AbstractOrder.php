<?php


namespace App\Models\Orders;


use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\Provider;
use App\Models\References\Nomenclature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class AbstractOrder extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'uuid',
        'number',
        'order_date',
        'deadline_date',
        'customer_status',
        'provider_status',
        'customer_id',
        'provider_id',
        'contractor_id',
    ];

    protected $hidden = [
        'uuid',
    ];

    const ACTION_DRAFT = 'draft';
    const ACTION_APPROVE = 'approve';

    const CUSTOMER_STATUS_NOT_AGREED = 'Не согласовано';
    const CUSTOMER_STATUS_AGREED = 'Согласовано';
    const CUSTOMER_STATUS_DRAFT = 'Черновик';
    const CUSTOMER_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const CUSTOMER_STATUS_CANCELED = 'Аннулировано';

    const PROVIDER_STATUS_NOT_AGREED = 'Не согласовано';
    const PROVIDER_STATUS_AGREED = 'Согласовано';
    const PROVIDER_STATUS_PARTIALLY_AGREED = 'Согласовано частично';
    const PROVIDER_STATUS_DRAFT = 'Черновик';
    const PROVIDER_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    const PROVIDER_STATUS_CANCELED = 'Аннулировано';

    public static function getCustomerStatuses(): array
    {
        return [
            self::CUSTOMER_STATUS_NOT_AGREED,
            self::CUSTOMER_STATUS_AGREED,
            self::CUSTOMER_STATUS_DRAFT,
            self::CUSTOMER_STATUS_UNDER_CONSIDERATION,
            self::CUSTOMER_STATUS_CANCELED,
        ];
    }

    public static function getProviderStatuses(): array
    {
        return [
            self::PROVIDER_STATUS_NOT_AGREED,
            self::PROVIDER_STATUS_AGREED,
            self::PROVIDER_STATUS_PARTIALLY_AGREED,
            self::PROVIDER_STATUS_DRAFT,
            self::PROVIDER_STATUS_UNDER_CONSIDERATION,
            self::PROVIDER_STATUS_CANCELED,
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(OrderPosition::class, 'order_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
    }
}
