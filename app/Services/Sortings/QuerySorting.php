<?php


namespace App\Services\Sortings;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class QuerySorting
{
    protected Builder $builder;
    protected string $order;

    public function __construct(protected Request $request)
    {
    }

    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        $this->order = $this->sort_order();
//        foreach ($this->available_columns() as $field) {
////            $method = 'sort_field';
//            if (!empty($this->sort_field()) && in_array($this->sort_field(), $this->available_columns())) {
//                $this->builder->orderBy($this->sort_field(), $this->sort_order());
//            }
//
//            if (method_exists($this, $method)) {
//                call_user_func_array([$this, $method], (array)$value);
//            }
//        }
//        foreach ($this->fields() as $field => $value) {
        $method = Str::snake($this->sort_field());
        if (method_exists($this, $method)) {
            call_user_func([$this, $method]);
        } else $this->default_order();
//        }
    }

    protected function sort_field()
    {
        return data_get($this->request, 'sort_field');
    }

    protected function sort_order()
    {
        $sort_order_value = Str::camel(data_get($this->request, 'sort_order'));
        return in_array($sort_order_value, ['asc', 'desc']) ? $sort_order_value : 'desc';
    }

    protected abstract function default_order();
}
