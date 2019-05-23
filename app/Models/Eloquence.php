<?php
namespace App\Models;
/**********************************************************************************
*   How to use
* =================
 * Collections:
 *  - collect()
 *    Get model to array collection with filterabel, sortable & pagination.
 * 
 * - filterable()
 *    Get model to array collection with filterabel on class filter.
 *    for default class filter is route list,
 *    ex: http://namesite/users -> App/Fiters/users::class
 * 
 *    Or you can define in model class with: 
 *    protected $filterable = (string) name class.
 * 
 * - sortable()
 *    Get model to array collection with sortable by request,
 *    name  : 'sort'
 *    order : 'order'
 * 
 * 
 ***********************************************************************************/

use Illuminate\Support\Facades\Input;
use Request;
use Route;

trait Eloquence
{
    protected $filterableClass = null;

    public function scopeCollect($query, $map = false)
    {
        $request = request();

        $input = $request->input();
        $limit = $request->get('limit', 50);
        
        // $query = $query->filterable();
        // $query = $query->sortable();
    
        $query = $query->paginate($limit);   
        
        // // $query->setAppends(['has_relationships']);
        // // dd($query->__get('items'));
        // dd($query);

        return $query;
    }

    public function scopeFilterable($query, $className = null)
    {
        if($className) $this->filterableClass = 'App\\Filters\\'. $className ;

        if(!$this->filterableClass)  $this->setClassByRoute();

        $query->where(function ($query){
            if($this->filterableClass){
                $class = new $this->filterableClass();
                $class_methods = get_class_methods($class);
                foreach($class_methods as $filter){
                    if (Input::get($filter) && trim(Input::get($filter) !== '') ) 
                    {
                        $query = $class->$filter($query, Input::get($filter));
                    }
                }
            }
        });

        return $query;
    }

    public function scopeSortable($query)
    {
        $request = request();
        $sort  = $request->get('sort');
        $order = $request->get('order', 'asc');
        
        if($sort) $query = $query->orderBy($sort, $order);
        else      $query = $query->latest()->orderBy('id','desc');
        
        return $query;
    }

    protected function setClassByRoute()
    {
        $arr = explode('/', Route::current()->uri());

        // dd('Filterable-Class',$arr);

        if(strtolower($arr[0]) == 'api'){
            $arr[0] = 'admin';

            if(strtolower($arr[1]) == 'v1') unset($arr[1]);
        }

        foreach ($arr as $key => $sub) 
        {
            $arr[$key] =  implode('', array_map("ucfirst", explode('-', $sub)));
        }
        
        $class = '\App\Filters\\'. str_replace(' ', '\\', implode(' ', $arr));

        if(class_exists($class))
        {
            $this->filterableClass = $class;
        }       
    }
    
    
}