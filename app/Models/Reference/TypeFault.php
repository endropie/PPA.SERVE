<?php

namespace App\Models\Reference;

use App\Models\Model;

class TypeFault extends Model
{
    protected $fillable = ['name', 'description'];

    protected $hidden = ['created_at', 'updated_at'];

    public function faults()
    {
        return $this->hasMany('App\Models\Reference\Fault');
    }

}
