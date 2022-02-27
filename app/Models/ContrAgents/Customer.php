<?php


namespace App\Models\ContrAgents;


use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'contr_agent_id',
        'work_agreement_id',
//        'work_agreement',
//        'work_agreement_date',
        'work_type',
        'object',
        'sub_object',
    ];

    public function name()
    {
        return $this->
    }

    public function document()
    {

    }
}
