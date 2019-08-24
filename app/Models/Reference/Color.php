<?php

namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Color extends Model
{
    use Filterable;

    protected $fillable = ['name', 'description'];

    protected $hidden = ['created_at', 'updated_at'];
}
