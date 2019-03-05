<?php

namespace App\Models\Income;

use App\Models\Model;

class PreDeliveryItem extends Model
{
   protected $fillable = [
      'item_id', 'unit_id', 'unit_rate', 'unit_qty', 'quantity'
   ];

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
}
 