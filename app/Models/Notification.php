<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'notificationable_id',
        'notificationable_type',
        'body',
        'header',
        'config_data',
    ];

    protected $casts = [
        'config_data' => 'array',
    ];

    public function notificationable()
    {
        return $this->morphTo();
    }
}
