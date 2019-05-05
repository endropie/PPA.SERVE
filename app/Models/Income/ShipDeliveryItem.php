<?php

namespace App\Models\Income;

use App\Models\Model;

class ShipDeliveryItem extends Model
{
   protected $fillable = [
      'pre_delivery_item_id', 'item_id', 'unit_id', 'unit_rate', 'quantity'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

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

   public function getUnitAmountAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
   }
}
 