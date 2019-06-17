<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Request;
use Route;
use App\Models\Eloquence;

class Model extends Eloquent
{
    use Eloquence;

    protected  $relationships = [];

    public function getIsRelationshipAttribute()
    {
        $relationships = $this->getRelationships();
        foreach ($relationships as $relationship => $text) {
            if(static::relate($relationship, $this)) {
                return true;
            }
        }
        return false;
    }

    public function getHasRelationshipAttribute()
    {
        return $this->relationship();
    }

    public static function relate ($relationship, $model) 
    {
        $find = $model->has($relationship)->find($model->getKey());
        return  (boolean) $find;
    }

    public function getRelationships ($parameter = null) 
    {
        if($parameter === null) $params = $this->relationships;
        else $params = is_string($parameter) ? [$parameter] : $parameter;
        
        $relationships = [];
        foreach ($params as $key => $value) {
            if(is_string($key)) {
                $relationships[$key] = is_string($value) ? $value : $key;
            }
            else $relationships[$value] = $value;
        }

        return $relationships;
    }

    public function relationship($parameter = null)
    {
        $relationships = $this->getRelationships($parameter);

        $counter = array();
        foreach ($relationships as $relationship => $text) {
            $relate = static::relate($relationship, $this);
            if($relate) $counter[$text] = $relate;
        }


        return collect($counter);
    }

    

    public static function XXrelateCount ($relationship, $model, $i = 0, $result = null) {

        if($result == null) $result = [];
        
        $relation = explode('.', $relationship);
        $function = $relation[$i];

        // if($i == 3) dd($i, $i == count($relation)-1, !property_exists($model, 'attributes'), $model);

        if ($i == count($relation)-1 ) {
            if (!property_exists($model, 'attributes')) {
                foreach ($model as $collection) {
                    if(!property_exists($collection->$function, 'attributes')) {
                        if($collection->$function()->count()) {
                            foreach ($collection->$function as $col) $result[] = $col;
                        }
                    }
                    else if($collection->$function) {
                        // dd($collection, $collection->$function->getKey());
                        $result[$collection->$function->getKey()] = $collection->$function;
                    }                    
                }
            }
            else {
                // if($i==3) dd(!property_exists($model->$function, 'attributes'));

                if(is_object($model->$function) && !property_exists($model->$function, 'attributes')) {
                    if($model->$function()->count()) {
                        foreach ($model->$function as $col) $result[] = $col;
                    }
                }
                else {
                    // dd($model, $model->$function->getKey());
                    
                    if($model->$function) $result[$model->$function->getKey()] = $model->$function;
                }
            }
        }
        else {
            if (!property_exists($model, 'attributes')) {
                foreach ($model as $key => $collection) {
                    if(!property_exists($collection->$function, 'attributes')) {
                        foreach ($collection->$function as $col) {
                            $result = static::relate($relationship, $col, $i+1, $result);
                        }
                    }
                    else $result = static::relate($relationship, $collection->$function, $i+1, $result);
                }
                
            }
            else $result = static::relate($relationship, $model->$function, $i+1, $result);
        }

        
        return $result;
    }
    
}


