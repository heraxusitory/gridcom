<?php


namespace App\Traits;


use App\Models\Notification;

trait UseNotification
{
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notificationable');
    }
}
