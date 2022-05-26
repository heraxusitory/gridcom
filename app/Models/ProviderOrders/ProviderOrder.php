<?php


namespace App\Models\ProviderOrders;


use App\Interfaces\Syncable;
use App\Models\ProviderOrders\Corrections\OrderCorrection;
use App\Models\ProviderOrders\Corrections\RequirementCorrection;
use App\Models\ProviderOrders\Positions\ActualProviderOrderPosition;
use App\Models\ProviderOrders\Positions\BaseProviderOrderPosition;
use App\Models\References\ContrAgent;
use App\Models\References\Organization;
use App\Traits\UseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ProviderOrder extends Model implements Syncable
{
    use UseNotification;

    protected $table = 'provider_orders';

    protected $fillable = [
        'uuid',
        'number',
        'order_date',
        'contract_number',
        'contract_date',
        'contract_stage',
        'provider_contr_agent_id',
        'organization_id',
        'responsible_full_name',
        'responsible_phone',
        'organization_comment'
    ];

    protected $with = [
        'base_positions.nomenclature',
        'actual_positions.nomenclature',
        'requirement_corrections.positions.nomenclature',
        'order_corrections.positions.nomenclature',
    ];

    private const STAGE_ONE = 1;
    private const STAGE_TWO = 2;
    private const STAGE_THREE = 3;
    private const STAGE_FOUR = 4;
    private const STAGE_FIVE = 5;
    private const STAGE_SIX = 6;
    private const STAGE_SEVEN = 7;

    private const HUMAN_READABLE_STAGE_ONE = 'Первый этап';
    private const HUMAN_READABLE_STAGE_TWO = 'Второй этап';
    private const HUMAN_READABLE_STAGE_THREE = 'Третий этап';
    private const HUMAN_READABLE_STAGE_FOUR = 'Четвертый этап';
    private const HUMAN_READABLE_STAGE_FIVE = 'Пятый этап';
    private const HUMAN_READABLE_STAGE_SIX = 'Шестой этап';
    private const HUMAN_READABLE_STAGE_SEVEN = 'Седьмой этап';

    public static function HUMAN_READABLE_STAGES()
    {
        return [
            self::STAGE_ONE => self::HUMAN_READABLE_STAGE_ONE,
            self::STAGE_TWO => self::HUMAN_READABLE_STAGE_TWO,
            self::STAGE_THREE => self::HUMAN_READABLE_STAGE_THREE,
            self::STAGE_FOUR => self::HUMAN_READABLE_STAGE_FOUR,
            self::STAGE_FIVE => self::HUMAN_READABLE_STAGE_FIVE,
            self::STAGE_SIX => self::HUMAN_READABLE_STAGE_SIX,
            self::STAGE_SEVEN => self::HUMAN_READABLE_STAGE_SEVEN,
        ];
    }

    public static function STAGES()
    {
        return [
            self::STAGE_ONE,
            self::STAGE_TWO,
            self::STAGE_THREE,
            self::STAGE_FOUR,
            self::STAGE_FIVE,
            self::STAGE_SIX,
            self::STAGE_SEVEN,
        ];
    }

    public function provider()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'provider_contr_agent_id');
    }

    public function organization()
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function actual_positions()
    {
        return $this->hasMany(ActualProviderOrderPosition::class, 'provider_order_id', 'id');
    }

    public function base_positions()
    {
        return $this->hasMany(BaseProviderOrderPosition::class, 'provider_order_id', 'id');
    }

    public function requirement_corrections()
    {
        return $this->hasMany(RequirementCorrection::class, 'provider_order_id', 'id');
    }

    public function order_corrections()
    {
        return $this->hasMany(OrderCorrection::class, 'provider_order_id', 'id');
    }

    public function getOrderDateAttribute($value)
    {
        return (new Carbon($value))->format('Y-m-d');
    }
}
