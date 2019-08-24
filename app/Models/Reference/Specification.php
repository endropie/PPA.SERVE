<?php

namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Specification extends Model
{
    use Filterable;

    protected $fillable = [
        'code', 'name', 'description', 'color_id', 'salt_white', 'salt_red'
        //'times_spray_white', 'times_spray_red'
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
