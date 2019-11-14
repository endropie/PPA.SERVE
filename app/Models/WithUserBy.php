<?php
namespace App\Models;

trait WithUserBy
{
    public static function bootWithUserBy()
	{
		static::creating(function ($model)
        {
            if ($user =  auth()->user()) {
                $model->created_by = $user->id;
            }
            return $model;
        });
	}

}
