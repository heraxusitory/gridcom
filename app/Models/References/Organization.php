<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organizations';

    protected $fillable = [
        'uuid',
        'name',
        'is_confirmed',
    ];

//    public function notifications()
//    {
//        return $this->belongsToMany(Notification::class,
//            NotificationToOrganization::class,
//            'notification_id',
//            'organization_id'
//        );
//    }
}
