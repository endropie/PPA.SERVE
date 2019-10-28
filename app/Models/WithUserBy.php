<?php
namespace App\Models;

use function GuzzleHttp\json_decode;

trait WithUserBy
{
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    public static function bootWithUserBy()
	{
		static::creating(function ($model)
        {
            if ($user =  \Auth::user()) {
                $model->created_by = $user->id;
            }
            return $model;
        });
	}

}
