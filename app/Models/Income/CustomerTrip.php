<?php

namespace App\Models\Income;

use App\Models\Model;

class CustomerTrip extends Model
{

    protected $fillable = [
        'intday', 'time'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function customer ()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }
}
