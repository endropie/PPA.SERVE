<?php

namespace App\Models;

use App\Filters\Filterable;
use App\Models\Model;

class Stateable extends Model
{
    use Filterable;

    protected $fillable = ['created_by', 'state'];

    public function stateable()
    {
        return $this->morphTo();
    }
}
