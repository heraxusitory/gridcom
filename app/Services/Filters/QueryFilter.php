<?php


namespace App\Services\Filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class QueryFilter
{

    protected Builder $builder;

    public function __construct(protected Request $request)
    {
    }

    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->fields() as $field => $value) {
//            dump((array)$value);
            $method = Str::snake($field);
            if (method_exists($this, $method)) {
                call_user_func_array([$this, $method], (array)$value);
            }
        }
    }

    protected function fields(): array
    {
        return array_filter(
            array_map(function ($item) {
                if (gettype($item) === 'string') {
                    return trim($item);
                }
                return $item;
            }, $this->request->all())
        );
    }
}
