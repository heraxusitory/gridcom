<?php


namespace App\Models\ProviderOrders\Corrections;


use App\Models\References\Nomenclature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RequirementCorrectionPosition extends Model
{
    protected $table = 'requirement_correction_positions';

    protected $fillable = [
        'position_id',
        'requirement_correction_id',
        'status',
        'nomenclature_id',
        'count',
        'price_without_vat',
        'amount_without_vat',
        'vat_rate',
        'amount_with_vat',
        'delta',
        'delivery_time',
        'delivery_address',
        'organization_comment',
        'provider_comment',
    ];

    private const STATUS_AGREED = 'Согласовано';
    private const STATUS_REJECTED = 'Не согласовано';
    private const STATUS_UNDER_CONSIDERATION = 'На рассмотрении';

    public static function getStatuses()
    {
        return [
            self::STATUS_AGREED(),
            self::STATUS_REJECTED(),
            self::STATUS_UNDER_CONSIDERATION(),
        ];
    }

    public static function STATUS_REJECTED()
    {
        return self::STATUS_REJECTED;
    }

    public static function STATUS_AGREED()
    {
        return self::STATUS_AGREED;
    }

    public static function STATUS_UNDER_CONSIDERATION()
    {
        return self::STATUS_UNDER_CONSIDERATION;
    }

    public function getDeliveryTimeAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }

    public function nomenclature()
    {
        return $this->hasOne(Nomenclature::class, 'id', 'nomenclature_id');
    }
}
