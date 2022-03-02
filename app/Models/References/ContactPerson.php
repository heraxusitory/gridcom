<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContactPerson extends Model
{
    protected $table = 'contact_persons';

    protected $fillable = [
        'contr_agent_id',
        'full_name',
        'email',
        'phone',
    ];

    public function contrAgentName(): belongsTo
    {
        return $this->belongsTo(ContrAgent::class, 'contr_agent_id', 'id');
    }
}
