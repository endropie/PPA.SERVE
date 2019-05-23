<?php

namespace App\Models\Income;

use App\Models\Model;

class PreDeliveryItem extends Model
{
   protected $fillable = [
      'item_id', 'unit_id', 'unit_rate', 'quantity'
   ];

   protected $appends = ['unit_amount', 'total_ship_delivery_item'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = ['item'];

   public function pre_delivery()
   {
      return $this->belongsTo('App\Models\Income\PreDelivery');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function ship_delivery_items()
   {
      return $this->hasMany('App\Models\Income\ShipDeliveryItem');
   }

   public function request_order_items() {
      return $this->morphMany('App\Models\Common\MountBaseItemable', 'mount')
         ->where('base_type', 'App\Models\Income\RequestOrderItem');
   }
   
   public function getTotalShipDeliveryItemAttribute() {
      $totals = 0;

      foreach ($this->ship_delivery_items as $detail) {
         if($detail->delivery_order_items->count() > 0) {
            $totals += (double) $detail->delivery_order_items->sum('unit_amount');
         }
         else {
            $totals += (double) $detail->unit_amount;
         }
      }

      return $totals;
   }

   public function getTotalRequestOrderItemAttribute() {
      return (double) $this->request_order_items->sum('unit_amount');
   }

   public function getUnitAmountAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
   }
}
 