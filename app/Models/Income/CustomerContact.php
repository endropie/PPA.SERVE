<?php

namespace App\Models\Income;

use App\Models\Model;

class CustomerContact extends Model
{

    protected $fillable = [
        'label', 'name', 'phone',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function customer ()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }
}
