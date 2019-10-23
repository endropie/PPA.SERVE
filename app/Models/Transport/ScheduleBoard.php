<?php
namespace App\Models\Transport;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Filters\Filterable;

class ScheduleBoard extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'vehicle_id', 'operator_id', 'date', 'time', 'destination',
    ];

    protected $relationships = [];

    protected $hidden = [];

    public function vehicle() {
        return $this->belongsTo('App\Models\Reference\Vehicle');
    }

    public function operator()
    {
        return $this->belongsTo('App\Models\Common\Employee', 'operator_id');
    }
}
