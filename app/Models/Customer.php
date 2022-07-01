<?php


namespace App\Models;


use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Organization;
use App\Models\References\WorkAgreementDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Customer extends Model
{
    protected $table = 'order_customers';

    protected $fillable = [
        'organization_id',
        'work_agreement_id',
        'work_type',
        'object_id',
        'sub_object_id',
        'work_start_date',
        'work_end_date',
    ];

    public function organization(): hasOne
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    public function work_agreement(): hasOne
    {
        return $this->hasOne(WorkAgreementDocument::class, 'id', 'work_agreement_id');
    }

    public function contract(): hasOne
    {
        return $this->hasOne(WorkAgreementDocument::class, 'id', 'work_agreement_id');
    }

    public function object(): hasOne
    {
        return $this->hasOne(CustomerObject::class, 'id', 'object_id');
    }

    public function subObject(): hasOne
    {
        return $this->hasOne(CustomerSubObject::class, 'id', 'sub_object_id');
    }


    public function getWorkStartDateAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }

    public function getWorkEndDateAttribute($value)
    {
        return !is_null($value) ? (new Carbon($value))->format('Y-m-d') : null;
    }
}
