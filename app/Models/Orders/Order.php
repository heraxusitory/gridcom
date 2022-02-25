<?php

namespace App\Models\Orders;

use App\Models\MtrPositions\MtrPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'is_external',
        'number',
        'order_date',
        'deadline_date',
        'customer_status',
        'provider_status',
        'customer_filial_branch',
        'work_agreement',
        'work_agreement_date',
        'work_type',
        'object',
        'sub_object',
        'provider',
        'provider_contract',
        'provider_contract_date',
        'provider_full_name',
        'provider_email',
        'provider_phone',
        'contractor',
        'contractor_full_name',
        'contractor_email',
        'contractor_phone',
        'contractor_responsible_full_name',
        'contractor_responsible_phone',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(MtrPosition::class, 'order_info_id', 'id');
    }
}
