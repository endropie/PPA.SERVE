<?php

namespace App\Models\Reference;

use App\Models\Model;

class Specification extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'thick', 'color_id', 'times_spray_white', 'times_spray_red'
    ];

    protected $hidden = ['created_at', 'updated_at'];
    
    public function specification_details()
    {
        return $this->hasMany('App\Models\Reference\SpecificationDetail');
    }

    public function color()
    {
        return $this->belongsTo('App\Models\Reference\Color');
    }
}
