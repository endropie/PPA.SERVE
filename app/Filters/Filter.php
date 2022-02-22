<?php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
class Filter
{
    protected $request;
    protected $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;

        if(request('--with')) $request->merge(['__with' => request('--with')]);
        if(request('--select')) $request->merge(['__select' => request('--select')]);
        if(request('--limit')) $request->merge(['__limit' => request('--limit')]);
        if(request('--offset')) $request->merge(['__offset' => request('--offset')]);
    }

    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        $fields = \Schema::getColumnListing($builder->getQuery()->from);

        foreach ($this->filters() as $name => $value) {

            if ( ! method_exists($this, $name)) {
                if(strlen($value) && in_array($name, $fields)) {
                    $this->builder->where($name, $value);
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

    public function __select($values = false) {
        // $with = ;
        if(empty($values)) return $this->builder;
        else {
            $fields = explode(';', $values);
            return $this->builder->select(array_merge(['id'], $fields));
        }
    }

    public function __limit($value = 50) {
        $value = (int) $value;
        return $this->builder->limit($value);
    }

    public function enable($value = 'true') {
        if($value == 'true') $value = 1;
        if($value == 'false') $value = 0;

        return $this->builder->where('enable', $value);
    }

    public function withTrashed($value = false) {

        if((int) $value !=  1) return $this->builder;
        else {
            return $this->builder->withTrashed();
        }
    }

    public function sort($value) {
        $order = $this->request->has('descending') ? 'desc' : 'asc';
        $fields = \Schema::getColumnListing($this->builder->getQuery()->from);

        if(method_exists($this, 'sort_'. $value)) {
            $function = 'sort_'. $value;
            return $this->$function($order);
        }
        else if (strlen($value) && in_array($value, $fields)){
            return $this->builder->orderBy($value, $order);
        }
        else return $this->builder;
    }

    public function search($value = '') {
        if(!strlen($value)) return $this->builder;

        $tableName = $this->builder->getQuery()->from;
        $fields = \Schema::getColumnListing($tableName);


        $except = [$this->builder->getModel()->getKeyName()];

        $fields = array_diff_key($fields, $except);

        if (strlen($this->request->get('search-keys', ''))) {
            $fields = explode(",", $this->request->get('search-keys'));
        }

        $separator = substr_count($value, '|') > 0 ? '|' : ' ';
        $keywords = explode($separator, $value);

        return $this->builder->where(function ($query) use ($fields, $keywords) {
            foreach ($keywords as $keyword) {
                if(strlen($keyword)) {
                  $query->where(function ($query) use ($fields, $keyword) {
                    foreach ($fields as $field) {
                        $query->orWhere($field, 'like', '%'.$keyword.'%');
                    }
                  });
                }
            }
        });
    }
}
