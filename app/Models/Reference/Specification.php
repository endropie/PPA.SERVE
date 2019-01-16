<?php

namespace App\Models\Reference;

use App\Models\Model;

class Specification extends Model
{
    protected $fillable = ['name', 'description', 'thick', 'colour_id', 'spray_white', 'spray_red'];

    protected $hidden = ['created_at', 'updated_at'];

    public function colour()
    {
        return $this->belongsTo('App\Models\Reference\Colour');
    }
}
