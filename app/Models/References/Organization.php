<?php


namespace App\Models\References;


use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organizations';

    protected $fillable = [
        'uuid',
        'name',
        'is_visible_to_client',
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
