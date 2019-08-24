<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
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

    public function withRelationship($values)
    {
        $this->relationships = array_merge($this->getRelationships(), $values);
        return $this->has_relationships;
    }

    public function withoutRelationship($values)
    {
        $relations = $this->getRelationships();
        foreach ($values as $value) {
            if(!empty($relations[$value])) unset($relations[$value]);
        }
        $this->relationships = $relations;

        return $this->has_relationships;
    }

    public function getTableColumns() {
        $builder = $this->getConnection()->getSchemaBuilder();
        $columns = $builder->getColumnListing($this->getTable());
        $columnsWithType = collect($columns)->mapWithKeys(function ($item, $key) use ($builder) {
            $key = $builder->getColumnType($this->getTable(), $item);
            return [$item => $key];
        });
        return $columnsWithType->toArray();
    }

}


