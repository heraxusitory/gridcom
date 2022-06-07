<?php


namespace App\Traits;


use App\Services\Sortings\QuerySorting;
use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    public static function scopeSorting(Builder $builder, QuerySorting $sorting)
    {
        $sorting->apply($builder);
    }
}
