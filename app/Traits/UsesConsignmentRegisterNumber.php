<?php


namespace App\Traits;


trait UsesConsignmentRegisterNumber
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
