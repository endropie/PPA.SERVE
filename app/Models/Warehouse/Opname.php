<?php

namespace App\Models\Warehouse;

use App\Filters\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\WithUserBy;

class Opname extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number',
    ];

    protected $appends = ['fullnumber'];

    public function opname_stocks()
    {
        return $this->hasMany('App\Models\Warehouse\OpnameStock')->withTrashed();
    }

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number ." R.". (int) $this->revise_number;

        return $this->number;
    }
}
