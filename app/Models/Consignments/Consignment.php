<?php


namespace App\Models\Consignments;


use App\Models\References\ContrAgent;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Traits\UsesConsignmentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consignment extends Model
{
    use HasFactory, UsesConsignmentNumber;

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

    private const ACTION_DRAFT = 'draft';
    private const ACTION_APPROVE = 'approve';

    public static function getActions(): array
    {
        return [
            self::ACTION_APPROVE,
            self::ACTION_DRAFT,
        ];
    }

//    public function order(): hasOne
//    {
//        return $this->hasOne(Order::class, 'id', 'order_id');
//    }

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

    public function positions()
    {
        return $this->hasMany(ConsignmentPosition::class, 'consignment_id', 'id');
    }
}
