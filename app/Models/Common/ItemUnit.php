<?php

namespace App\Models\Common;

use App\Models\Model;

class ItemUnit extends Model
{
    protected $appends = ['decimal_in'];

    protected $fillable = ['unit_id', 'rate'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'rate' => 'double',
    ];

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getDecimalInAttribute()
    {
        return (double) $this->unit->decimal_in;
    }
}
