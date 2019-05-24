<?php

namespace App\Models\Income;

use App\Models\Model;

class DeliveryOrderItem extends Model
{
   protected $fillable = [
      'item_id', 'unit_id', 'unit_rate', 'quantity'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = [];

   public function delivery_order()
   {
      return $this->belongsTo('App\Models\Income\DeliveryOrder');
   }

   // public function request_order_item()
   // {
   //    return $this->belongsTo('App\Models\Income\RequestOrderItem');
   // }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function ship_delivery_items() {
      return $this->morphMany('App\Models\Common\MountBaseItemable', 'mount')
         ->where('base_type', 'App\Models\Income\ShipDeliveryItem');
   }

   public function request_order_item() {
      return $this->morphOne('App\Models\Common\MountBaseItemable', 'mount')
         ->where('base_type', 'App\Models\Income\RequestOrderItem');
   }

   public function getTotalShipDeliveryItemAttribute() {
      return (double) $this->ship_delivery_items->sum('unit_amount');
   }

   public function getTotalRequestOrderItemAttribute() {
      return (double) $this->request_order_item->unit_amount; 
   }

   public function getUnitAmountAttribute() {
      return (double) $this->quantity * $this->unit_rate;
   }
}
 