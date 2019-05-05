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

   protected $model_relations = [];

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

   public function mount_extractables() // Type of Morph is "base" or "mount"
   {
      return $this->morphMany('App\Models\Common\ItemExtractable', 'mount');
   }

   public function base_request_order_items() {
      return $this->morphMany('App\Models\Common\ItemExtractable', 'mount')
         ->where('base_type', 'App\Models\Income\RequestOrderItem');
   }

   public function getTotalShipDeliveryItemAttribute() {
      // return false when rate is not valid
      // $details = $this->ship_delivery_items;
      return (double) $this->ship_delivery_items->sum('unit_amount');
   }

   public function getUnitAmountAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
   }
}
 