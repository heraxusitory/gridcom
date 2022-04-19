<?php


namespace App\Models\References;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContrAgent extends Model
{
    protected $table = 'contr_agents';

    protected $fillable = [
        'uuid',
        'name',
        //поле отвечает за то, что если запись создается автоматически (не по обмену из АС МТО), то оно является неподтвержденным
//        'is_confirmed',
        'is_visible_to_client',
    ];

    protected $hidden = [
        'uuid'
    ];

    public function contacts(): hasMany
    {
        return $this->hasMany(ContactPerson::class, 'contr_agent_id', 'id');
    }

    public function lkk_local_contacts()
    {

    }

    public function users()
    {
        return $this->belongsToMany(User::class, UserToContrAgent::class, 'user_id', 'contr_agent_id');

    }
}
