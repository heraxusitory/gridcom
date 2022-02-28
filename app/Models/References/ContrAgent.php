<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContrAgent extends Model
{
    protected $table = 'contr_agents';

    protected $fillable = [
      'name',
    ];

    public function contacts(): hasMany
    {
        return $this->hasMany(ContactPerson::class, 'contr_agent_id', 'id');
    }
}
