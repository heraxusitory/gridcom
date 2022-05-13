<?php

namespace App\Models;

use App\Interfaces\SyncStackable;
use App\Models\References\ContrAgent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class IntegrationUser extends Authenticatable implements SyncStackable
{
    use HasApiTokens, HasFactory;

    protected $table = 'integration_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'contr_agent_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function contr_agent()
    {
        return $this->hasOne(ContrAgent::class, 'id', 'contr_agent_id');
    }


    public function hasContrAgent()
    {
        return (bool)$this->contr_agent()->first();
    }

    public function isProvider()
    {
        return $this->role === 'provider';
    }

    public function isContractor()
    {
        return $this->role === 'contractor';
    }
}
