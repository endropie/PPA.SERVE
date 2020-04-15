<?php
namespace App\Models;

trait WithUserBy
{

    public function created_user()
	{
        return $this->belongsTo('App\Models\Auth\User', 'created_by');
    }

    public function updated_user()
	{
        return $this->belongsTo('App\Models\Auth\User', 'updated_by');
    }

    public static function bootWithUserBy()
	{
		static::creating(function ($model)
        {
            $field = 'created_by';
            if ($user =  auth()->user()) {
                $model->$field = $user->id;
            }
            return $model;
        });

        static::saving(function ($model)
        {
            $field = 'updated_by';
            if (\Schema::hasColumn($model->getTable(), $field)) {
                if ($user =  auth()->user()) {
                    $model->$field = $user->id;
                }
            }
            return $model;
        });
	}

}
