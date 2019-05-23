<?php

namespace App\Models\Factory;

use App\Models\Model;

class PackingItem extends Model
{
    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'unit_rate', 'type_fault_id', 'work_order_item_id'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    public function packing_item_faults()
    {
        return $this->hasMany('App\Models\Factory\PackingItemFault');
    }

    public function work_order_item()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrderItem');
    }

    public function packing()
    {
        return $this->belongsTo('App\Models\Factory\Packing');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function unit()
    {
       return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getUnitAmountAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
    }
}
