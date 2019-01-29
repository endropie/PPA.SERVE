<?php

namespace App\Models\Reference;

use App\Models\Model;

class Specification extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'thick', 'color_id', 'times_spray_white', 'times_spray_red', 
        'thick_1', 'thick_2', 'thick_3', 'thick_4', 'plate_1', 'plate_2', 'plate_3', 'plate_4'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function color()
    {
        return $this->belongsTo('App\Models\Reference\Color');
    }
}
