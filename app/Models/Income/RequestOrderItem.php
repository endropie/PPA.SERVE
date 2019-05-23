<?php

namespace App\Models\Income;

use App\Models\Model;

class RequestOrderItem extends Model
{
   protected $fillable = [
      'item_id', 'unit_id', 'unit_rate', 'quantity', 'price'
   ];

   protected $appends = ['unit_amount', 'total_pre_delivery_item', 'total_delivery_order_item'];
   
   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = [];

   public function request_order()
   {
      return $this->belongsTo('App\Models\Income\RequestOrder');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item')->with('unit');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function pre_delivery_items() {
      return $this->morphMany('App\Models\Common\MountBaseItemable', 'base')
         ->where('mount_type', 'App\Models\Income\PreDeliveryItem');
   }

   public function delivery_order_items() {
      return $this->morphMany('App\Models\Common\MountBaseItemable', 'base')
         ->where('mount_type', 'App\Models\Income\DeliveryOrderItem');
   }

   public function getTotalPreDeliveryItemAttribute() {      
      return (double) $this->pre_delivery_items->sum('unit_amount');
   }

   public function getTotalDeliveryOrderItemAttribute() {      
      return (double) $this->delivery_order_items->sum('unit_amount');
   }

   public function getUnitAmountAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
   }
}
 