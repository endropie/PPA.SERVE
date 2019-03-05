<?php

namespace App\Models\Factory;

use App\Models\Model;

class PackingItem extends Model
{
    protected $fillable = [
        'number', 'customer_id', 'date', 'time', 'shift_id', 'description',
        'type_worktime_id', 'operator_id', 'work_order_id', 
        'item_id', 'quantity', 'unit_id', 'unit_rate', 'type_fault_id'
    ];

    protected $appends = ['unit_stock'];

    protected $hidden = ['created_at', 'updated_at'];

    public function packing_item_faults()
    {
        return $this->hasMany('App\Models\Factory\PackingItemFault');
    }

    public function work_order()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrder');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function operator()
    {
        return $this->belongsTo('App\Models\Reference\Operator');
    }

    public function shift()
    {
        return $this->belongsTo('App\Models\Reference\Shift');
    }

    public function type_fault()
    {
        return $this->belongsTo('App\Models\Reference\TypeFault');
    }

    public function type_worktime()
    {
        return $this->belongsTo('App\Models\Reference\TypeWorktime');
    }

    public function getUnitStockAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity / $this->unit_rate;
    }
}
