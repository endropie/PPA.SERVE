<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkOrderItem extends Model
{
   protected $fillable = [
      'item_id', 'quantity', 'unit_id', 'target', 'unit_rate', 'ngratio'
   ];

   protected $appends = ['unit_amount', 'total_packing_item'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = [];

   public function work_order_item_lines()
   {
      return $this->hasMany('App\Models\Factory\WorkOrderItemLine');
   }

   public function packing_items() 
   {
      return $this->hasMany('App\Models\Factory\PackingItem');
   }

   public function work_order()
   {
      return $this->belongsTo('App\Models\Factory\WorkOrder');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function stockable()
   {
      return $this->morphMany('App\Models\Common\ItemStockable', 'base');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function workin_production_items()
   {
      return $this->hasMany('App\Models\Factory\WorkinProductionItem');
   }

   public function getTotalPackingItemAttribute()
   {
      return $this->packing_items->sum('unit_amount');
   }

   public function getUnitAmountAttribute() 
   {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return null;
      
      return (double) $this->quantity * $this->unit_rate;
   }
}
 