<?php

namespace App\Models\Income;

use App\Models\Model;

class PreDeliveryItem extends Model
{
   protected $fillable = [
      'item_id', 'unit_id', 'unit_rate', 'quantity'
   ];

   protected $appends = ['unit_amount'];

   protected $hidden = ['created_at', 'updated_at'];

   public function pre_delivery()
   {
      return $this->belongsTo('App\Models\Income\PreDelivery');
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

   public function getUnitAmountAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
   }
}
 