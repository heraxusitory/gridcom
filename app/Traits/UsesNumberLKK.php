<?php


namespace App\Traits;


use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Auth;

trait UsesNumberLKK
{
    protected static function booted()
    {
        static::created(function ($model) {
            if (Auth::guard('webapi')->check()) {
//                dd($model->id);
//                $last_increment_id = $model->newQuery()->max('id');
//                $last_increment_id++;
                $model->number = (string)config('lkk.prefix_lkk_number') . $model->id;
                $model->save();
            }
        });
    }
}
