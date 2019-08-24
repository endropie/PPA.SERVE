<?php
namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Vehicle extends Model
{
    use Filterable;

    protected $fillable = ['number', 'type', 'owner', 'department_id', 'description'];

    protected $hidden = ['created_at', 'updated_at'];

    public function department () {
        return $this->belongsTo('App\Models\Reference\Department');
    }
}
