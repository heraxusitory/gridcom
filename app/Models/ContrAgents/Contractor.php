<?php


namespace App\Models\ContrAgents;


use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $table = 'contractors';
    protected $fillable = [
        'contr_agent_id',
        'contractor_full_name',
        'contractor_email',
        'contractor_phone',
        'contractor_responsible_full_name',
        'contractor_responsible_phone',
    ];
}
