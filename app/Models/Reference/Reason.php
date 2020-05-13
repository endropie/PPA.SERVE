<?php

namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Reason extends Model
{
    use Filterable;

    protected $fillable = ['name', 'enable'];

    protected $hidden = ['created_at', 'updated_at'];
}
