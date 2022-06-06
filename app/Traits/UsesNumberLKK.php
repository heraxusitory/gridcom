<?php


namespace App\Traits;


use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Auth;

trait UsesNumberLKK
{
    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::guard('webapi')->check()) {
                $last_increment_id = $model->newQuery()->max('id');
                $last_increment_id++;
                $model->number = (string)config('mto_lkk.prefix_lkk_number') . $last_increment_id;
            }
        });
    }
}
