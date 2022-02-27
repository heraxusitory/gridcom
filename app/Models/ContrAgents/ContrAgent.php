<?php


namespace App\Models\ContrAgents;


use Illuminate\Database\Eloquent\Model;

class ContrAgent extends Model
{
    protected $table = 'contr_agents';

    protected $fillable = [
        'name',
    ];
}
