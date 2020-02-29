<?php
namespace App\Models\Transport;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Filters\Filterable;
use App\Traits\GenerateNumber;
use App\Traits\Recurring;
use Bkwld\Cloner\Cloneable;

class ScheduleBoard extends Model
{
    use Filterable, SoftDeletes, WithUserBy, Recurring, GenerateNumber, Cloneable;

    protected $fillable = [
        'number', 'vehicle_id', 'operator_id', 'date', 'time', 'customer_id'
    ];

    protected $relationships = [];

    protected $cloneable_relations = ['customers'];

    protected $hidden = ['created_at', 'updated_at'];

    // public function customers()
    // {
    //     return $this->belongsToMany('App\Models\Income\Customer', 'schedule_board_customers')->using('App\Models\Transport\ScheduleBoardCustomer');
    // }

    public function customer() {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function vehicle() {
        return $this->belongsTo('App\Models\Reference\Vehicle');
    }

    public function operator()
    {
        return $this->belongsTo('App\Models\Common\Employee', 'operator_id');
    }

    public function recurring()
    {
        return $this->morphOne('App\Models\Common\Recurring', 'recurable');
    }

    public function hasRecurring($date = null)
    {
        $date = $date->format('Y-m-d');

        return (bool)
            $this->where('cloned_id', $this->id)
                 ->where('date', $date)
                 ->count();
    }

    public function onCloning($model, $child = null)
    {
        $date = now()->format('Y-m-d');
        $number = $this->getNextScheduleBoardNumber($date);

        $this->number = $number;
        $this->date = $date;
        $this->status = 'OPEN';
        $this->departed_at = null;
        $this->arrived_at = null;
        $this->cloned_id = $model->id;
    }

}
