<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryTask extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'date', 'trip_time', 'transaction', 'customer_id', 'description'
    ];

    protected $appends = ['fullnumber', 'is_overtime', 'is_loaded', 'is_checkout'];

    public function delivery_task_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryTaskItem')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function incoming_good() {
        return $this->hasOne('App\Models\Warehouse\IncomingGood');
    }

    public function getFullnumberAttribute()
    {
        // if ($this->revise_number) return $this->number ." R.". (int) $this->revise_number;

        return $this->number;
    }

    public function getIsLoadedAttribute()
    {
        return (boolean) app(\App\Models\Income\DeliveryLoad::class)
            ->where('date', $this->date)
            ->where('trip_time', $this->trip_time)
            ->count();
    }

    public function getIsCheckoutAttribute()
    {
        return (boolean) app(\App\Models\Income\DeliveryLoad::class)
            ->where('date', $this->date)
            ->where('trip_time', $this->trip_time)
            ->whereNotNull('delivery_checkout_id')
            ->count();
    }

    public function getIsOvertimeAttribute()
    {
        return strtotime(date('Y-m-d H:i:s')) - strtotime($this->date ." ". $this->trip_time) > 0;
    }
}
