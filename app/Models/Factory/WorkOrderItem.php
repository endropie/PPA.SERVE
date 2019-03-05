<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkOrderItem extends Model
{
   protected $fillable = [
      'work_order_id', 'item_id', 'quantity', 'unit_id', 'unit_rate', 'ngratio', 'line_id', 'shift_id', 'start_date', 'end_date'
   ];

   protected $appends = ['unit_stock'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function work_order()
   {
      return $this->belongsTo('App\Models\Factory\WorkOrder');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function line()
   {
      return $this->belongsTo('App\Models\Reference\Line');
   }

   public function getUnitStockAttribute() {

      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity / $this->unit_rate;
   }
}
 