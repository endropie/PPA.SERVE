<?php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
class QueryFilters
{
    protected $request;
    protected $builder;
  
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
  
    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        foreach ($this->filters() as $name => $value) {
            if ( ! method_exists($this, $name)) {
                continue;
            }
            if (strlen($value)) {
                $this->$name($value);
            } else {
                $this->$name();
            }
        }
        return $this->builder;
    }
  
    public function filters()
    {
        return $this->request->all();
    }

    

    public function _with($values) {
        // $with = ;
        $values = explode(';', $values);
        foreach ($values as $value) {
            $item = explode(':', $value);
            $name = $item[0];
            $with[$name] = function($q) use ($item) {
                if(!empty($item[1])) {
                    $fields = explode(',', $item[1]);
                    $q->select( array_merge(['id'], $fields));
                }
            };

        }
        return $this->builder->with($with);

        // return $this->builder->with('customer')->with($values);
    }

    public function _select($values = false) {
        // $with = ;
        if(empty($values)) return $this->builder;
        else {
            $fields = explode(',', $values);
            return $this->builder->select(array_merge(['id'], $fields));
        }
    }
}