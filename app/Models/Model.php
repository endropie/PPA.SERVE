<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Request;
use Route;

use App\Traits\Eloquence;

class Model extends Eloquent
{
    use Eloquence;

    public $model_comments = [];

    public function getModelComments()
    {
        return $this->model_comments;
    }

    public function pushModelComments($text)
    {
        return $this->model_comments[] = $text;
    }

    public function getIsRelatedAttribute()
    {
        return false;
    }
}
