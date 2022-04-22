<?php


namespace App\Models\ProviderOrders\Corrections;


use Illuminate\Database\Eloquent\Model;

class RequirementCorrectionPosition extends Model
{
    protected $table = 'requirement_correction_positions';

    protected $fillable = [
        'position_id',
        'requirement_correction_id',
        'status',
        'nomenclature_id',
        'count',
        'amount_without_vat',
        'vat_rate',
        'amount_with_vat',
        'delivery_time',
        'delivery_address',
        'organization_comment',
        'provider_comment',
    ];

    private const STATUS_AGREED = 'Согласовано';
    private const STATUS_REJECTED = 'Отклонено';

    public static function getStatuses()
    {
        return [
            self::STATUS_AGREED(),
            self::STATUS_REJECTED(),
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
}
