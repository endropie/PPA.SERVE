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
        
        if(request('--with')) $request->merge(['__with' => request('--with')]);
        if(request('--scope')) $request->merge(['__scope' => request('--scope')]);
        if(request('--select')) $request->merge(['__select' => request('--select')]);
    }
  
    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        $fields = \Schema::getColumnListing($builder->getQuery()->from);

        foreach ($this->filters() as $name => $value) {
            if ( ! method_exists($this, $name)) {
                if(strlen($value) && in_array($name, $fields)) {
                    return $this->builder->where($name, $value);
                }
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

    public function __with($values) {
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

    public function __scope($values) {
        // $with = ;
        $values = explode(';', $values);
        
        return $this->builder->where(function ($query) use ($values) {
            foreach ($values as $function) {
                $query->$function();
            }                
        });
    }

    public function __select($values = false) {
        // $with = ;
        if(empty($values)) return $this->builder;
        else {
            $fields = explode(';', $values);
            return $this->builder->select(array_merge(['id'], $fields));
        }
    }

    public function sort($value) {
        $order = $this->request->has('descending') ? 'desc' : 'asc';
        $fields = \Schema::getColumnListing($this->builder->getQuery()->from);
         
        if(method_exists($this, 'sort_'. $value)) {
            $function = 'sort_'. $value;
            return $this->$function($value, $order);
        }
        else if (strlen($value) && in_array($value, $fields)){
            return $this->builder->orderBy($value, $order);
        }
        else return $this->builder;
    }
}