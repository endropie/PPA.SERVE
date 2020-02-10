<?php

namespace App\Models\Warehouse;

use App\Filters\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class Opname extends Model
{
    use Filterable, SoftDeletes;

    protected $fillable = [
        'number',
    ];

    protected $appends = ['fullnumber'];

    protected $hidden = ['created_at', 'updated_at'];

    public function opname_stocks()
    {
        return $this->hasMany('App\Models\Warehouse\OpnameStock')->withTrashed();
    }

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number ." REV.". (int) $this->revise_number;

        return $this->number;
    }
}
