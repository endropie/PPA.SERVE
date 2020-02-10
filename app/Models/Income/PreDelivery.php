<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreDelivery extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'customer_id', 'description',
        'transaction', 'order_mode', 'rit', 'date', // 'plan_begin_date', 'plan_until_date'
    ];

    protected $appends = ['fullnumber'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [
        'pre_delivery_items.outgoing_verifications'
    ];

    public function pre_delivery_items()
    {
        return $this->hasMany('App\Models\Income\PreDeliveryItem')->withTrashed();
    }

    public function schedules()
    {
        return $this->belongsToMany('App\Models\Transport\ScheduleBoard', 'pre_delivery_schedules')->using('App\Models\Income\PreDeliverySchedule');
    }

    public function incoming_good() {
        return $this->hasOne('App\Models\Warehouse\IncomingGood');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number ." REV.". (int) $this->revise_number;

        return $this->number;
    }
}
