<?php


namespace App\Traits;


trait UsesNumberLKK
{
    protected static function booted()
    {
        static::creating(function ($model) {
            $last_increment_id = $model->newQuery()->max('id');
            $last_increment_id++;
            $model->number = (string)config('mto_lkk.prefix_lkk_number') . $last_increment_id;
        });
    }
}
