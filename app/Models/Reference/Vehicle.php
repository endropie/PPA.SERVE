<?php
namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Vehicle extends Model
{
    use Filterable;

    protected $fillable = ['number', 'type', 'owner', 'department_id', 'description', 'is_scheduled'];

    protected $hidden = ['created_at', 'updated_at'];

    public function department () {
        return $this->belongsTo('App\Models\Reference\Department');
    }

    public function schedule_boards() {
        return $this->hasMany('App\Models\Transport\ScheduleBoard');
    }

    public function getScheduledAttribute()
    {
        return $this->hasMany('App\Models\Transport\ScheduleBoard')->with('customers')->where('status', '<>', 'CLOSED')->first();
    }
}
