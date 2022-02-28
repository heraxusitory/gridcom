<?php


namespace App\Models;


use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Organization;
use App\Models\References\WorkAgreementDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $table = 'order_customers';

    protected $fillable = [
        'organization_id',
        'work_agreement_id',
        'work_type',
        'object_id',
        'sub_object_id',
    ];

    public function organization(): hasOne
    {
        return $this->hasOne(Organization::class, 'organization_id', 'id');
    }

    public function contract(): hasOne
    {
        return $this->hasOne(WorkAgreementDocument::class, 'works_agreement', 'id');
    }

    public function object(): hasOne
    {
        return $this->hasOne(CustomerObject::class, 'object_id', 'id');
    }

    public function subObject(): hasOne
    {
        return $this->hasOne(CustomerSubObject::class, 'sub_object_id', 'id');
    }
}
