<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;

class ShipDeliveryItem extends Model
{
   use Filterable;

   protected $appends = ['total_delivery_order_item'];

   protected $fillable = [
      'item_id', 'unit_id', 'unit_rate', 'quantity',
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = [
      'ship_delivery' => 'ship_delivery',
   ];

   public function ship_delivery()
   {
      return $this->belongsTo('App\Models\Income\ShipDelivery');
   }

   public function pre_delivery_item()
   {
      return $this->belongsTo('App\Models\Income\PreDeliveryItem');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function delivery_order_items() {
      return $this->morphMany('App\Models\Common\MountBaseItemable', 'base')
         ->where('mount_type', 'App\Models\Income\DeliveryOrderItem');
   }

   public function getTotalDeliveryOrderItemAttribute() {
      
      return (double) $this->delivery_order_items->sum('unit_amount');
   }

   public function getUnitAmountAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
   }

   public function scopeWait($query) {
      $query->whereNull('ship_delivery_id');
   }

   public function scopeDelivered($query) {
      // $query->where('id', '>', 1);
      $query->whereNotNull('ship_delivery_id');
   }
}
 