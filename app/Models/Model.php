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

    public function getIsRelatedAttribute()
    {
        return false;
    }
}
