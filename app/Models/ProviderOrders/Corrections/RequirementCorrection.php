<?php


namespace App\Models\ProviderOrders\Corrections;


use Illuminate\Database\Eloquent\Model;

class RequirementCorrection extends Model
{
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

    public static function STATUS_REJECTED()
    {

    }

    public function positions()
    {
        return $this->hasMany(RequirementCorrectionPosition::class, 'requirement_correction_id', 'id');
    }
}
