<?php

namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Line extends Model
{
    use Filterable;

    protected $fillable = ['name', 'ismain', 'load_capacity', 'description'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'load_capacity' => 'double'
    ];
}
