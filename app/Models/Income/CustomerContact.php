<?php

namespace App\Models\Income;

use App\Models\Model;

class CustomerContact extends Model
{
    
    protected $fillable = [
        'label', 'name', 'phone',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = ['items'];

    public function customer_contacts()
    {
        return $this->hasMany('App\Models\Income\CustomerContact');
    }  
}
