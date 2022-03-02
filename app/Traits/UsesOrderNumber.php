<?php


namespace App\Traits;


use Illuminate\Support\Facades\Log;

trait UsesOrderNumber
{
    protected static function booted()
    {
        static::creating(function ($model) {
            $last_increment_id = $model->newQuery()->max('id');
            $last_increment_id++;
            $model->number = (string)env('ORDER_PREFIX_NUMBER') . $last_increment_id;
        });
    }
}
