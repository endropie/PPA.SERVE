<?php

namespace App\Models\Common;

use App\Models\Model;

class XXMountBaseItemable extends Model
{
    protected $fillable = [
        'unit_amount', 'base_id', 'base_type'
    ];

    protected $attributes = [];

    public function base()
    {
        return $this->morphTo();
    }

    public function mount()
    {
        return $this->morphTo();
    }
}
 