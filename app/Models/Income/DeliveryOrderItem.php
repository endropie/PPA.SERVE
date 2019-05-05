<?php

namespace App\Models\Income;

use App\Models\Model;

class DeliveryOrderItem extends Model
{
   protected $fillable = [
      'ship_delivery_item_id', 'item_id', 'unit_id', 'unit_rate', 'quantity'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function delivery_order()
   {
      return $this->belongsTo('App\Models\Income\DeliveryOrder');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function mount_extractables() // Type of Morph is "base" or "mount"
   {
      return $this->morphMany('App\Models\Common\ItemExtractable', 'mount');
   }

   public function base_request_order_items() {
      return $this->morphMany('App\Models\Common\ItemExtractable', 'mount')
         ->where('base_type', 'App\Models\Income\RequestOrderItem');
   }

   public function getUnitAmountAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
   }
}
 