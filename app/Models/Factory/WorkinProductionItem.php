<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkinProductionItem extends Model
{
   protected $fillable = [
      'item_id', 'quantity', 'unit_id', 'unit_rate'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = [];

   public function workin_production()
   {
      return $this->belongsTo('App\Models\Factory\WorkinProduction');
   }

   public function work_order_item()
   {
      return $this->belongsTo('App\Models\Factory\WorkOrderItem');
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
 