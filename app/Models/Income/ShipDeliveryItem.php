<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;

class ShipDeliveryItem extends Model
{
   use Filterable;

   protected $appends = ['unit_amount'];

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

   public function scopeWait($query) {
      return $query->doesntHave('ship_delivery');
   }
}
 