<?php


namespace App\Models\ProviderOrders\Corrections;


use App\Models\ProviderOrders\ProviderOrder;
use App\Traits\UseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RequirementCorrection extends Model
{
    use UseNotification;

    protected $table = 'requirement_corrections';

    protected $fillable = [
        'provider_order_id',
        'correction_id',
        'date',
        'number',
        'provider_status',
    ];

    private const PROVIDER_STATUS_AGREED = 'Согласовано';
    private const PROVIDER_STATUS_NOT_AGREED = 'Не согласовано';
    private const PROVIDER_STATUS_UNDER_CONSIDERATION = 'На рассмотрении';
    private const PROVIDER_STATUS_PARTIALLY_AGREED = 'Согласовано частично';

    public static function PROVIDER_STATUS_AGREED()
    {
        return self::PROVIDER_STATUS_AGREED;
    }

    public static function PROVIDER_STATUS_NOT_AGREED()
    {
        return self::PROVIDER_STATUS_NOT_AGREED;
    }

    public static function PROVIDER_STATUS_UNDER_CONSIDERATION()
    {
        return self::PROVIDER_STATUS_UNDER_CONSIDERATION;
    }

    public static function PROVIDER_STATUS_PARTIALLY_AGREED()
    {
        return self::PROVIDER_STATUS_PARTIALLY_AGREED;
    }

    public static function getProviderStatuses()
    {
        return [
            self::PROVIDER_STATUS_NOT_AGREED(),
            self::PROVIDER_STATUS_UNDER_CONSIDERATION(),
            self::PROVIDER_STATUS_AGREED(),
            self::PROVIDER_STATUS_PARTIALLY_AGREED()
        ];
    }

    public static function STATUS_REJECTED()
    {

    }

    public function positions()
    {
        return $this->hasMany(RequirementCorrectionPosition::class, 'requirement_correction_id', 'id');
    }

    public function provider_order()
    {
        return $this->belongsTo(ProviderOrder::class, 'provider_order_id', 'id');
    }

    public function getDateAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }
}
