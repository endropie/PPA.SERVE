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
        foreach ($this->relationships as $relationship => $text) {
            if(count(static::relate($relationship, $this)) > 0) {
                return true;
            }
        }
        return false;
    }

    public function getHasRelationshipAttribute()
    {
        return $this->relationship()
        ->map(function($col, $key){
            return count($col);
        });
    }

    public static function relate ($relationship, $model, $i = 0, $result = null) {

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

    public function relationship($parameter = null)
    {
        $baseModel = $this;
        if($parameter === null) $params = $this->relationships;
        else $params = is_string($parameter) ? [$parameter] : $parameter;
        
        $relationships = [];
        foreach ($params as $key => $value) {
            if(is_string($key)) {
                $relationships[$key] = is_string($value) ? $value : $key;
            }
            else $relationships[$value] = $value;
        }

        $counter = array();
        foreach ($relationships as $relationship => $text) {
            $relation = explode('.', $relationship);
            $relate = static::relate($relationship, $baseModel);
            if(count($relate) > 0) {
                if (is_string($parameter)) {
                    return collect($relate);
                }
                else $counter[$text] = array_values($relate);
            }
        }


        return collect($counter);
    }

    public function Xrelationship($values = []) 
    {
        $baseModel = $this;
        if (!is_array($values) || count($values) === 0) $values = $this->relationships;

        $relationships = [];
        foreach ($values as $key => $value) {
            if(!is_string($key)) $key = $value;
            $relationships[$key] = $value;
        }

        $counter = array();
        foreach ($relationships as $relationship => $text) {
            $relation = explode('.', $relationship);
            
            $model = $baseModel;
            for ($i=0; $i < count($relation); $i++) { 
                $function = $relation[$i];
                
                if($i!=0){
                   dd( $model->$function );
                }
                else{
                    $model = $model->$function();
                }
                
                if ($i == count($relation)-1 && $c = $model->count()) {
                    $counter[] = $c . ' ' . strtolower($text);
                }
            }
        }
        return $counter;
    }
    
}


