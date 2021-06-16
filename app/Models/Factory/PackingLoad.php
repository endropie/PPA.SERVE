<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;

class PackingLoad extends Model
{
    use Filterable, SoftDeletes, WithUserBy, HasCommentable;

    protected $fillable = [
        'number', 'description'
    ];

    protected $appends = ['fullnumber'];

    protected $hidden = ['updated_at'];

    public function packing_load_items()
    {
        return $this->hasMany('App\Models\Factory\PackingLoadItem')->withTrashed();
    }

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number ." R.". (int) $this->revise_number;

        return $this->number;
    }
}
