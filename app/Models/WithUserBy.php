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
            $model->created_by = \Auth::user()->id ?? null;
            return $model;
        });
	}

}
