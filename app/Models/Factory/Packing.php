<?php

namespace App\Models\Factory;

use App\Models\Model;

class Packing extends Model
{
    protected $fillable = [
        'number', 'customer_id', 'date', 'time', 'shift_id', 'description',
        'worktime', 'operator_id', 'work_order_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function packing_items()
    {
        return $this->hasOne('App\Models\Factory\PackingItem');
    }

    public function work_order()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrder');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function operator()
    {
        return $this->belongsTo('App\Models\Reference\Operator');
    }

    public function shift()
    {
        return $this->belongsTo('App\Models\Reference\Shift');
    }
}
