<?php

namespace App\Models\Factory;

use App\Models\Model;
use App\Filters\Filterable;

class WorkOrder extends Model
{
   use Filterable;

   protected $fillable = [
      'number', 'line_id', 'stockist_from', 'description', 
   ];

   protected $hidden = ['created_at', 'updated_at'];

   public function work_order_items()
   {
      return $this->hasMany('App\Models\Factory\WorkOrderItem');
   }

   public function work_order_item_lines()
    {
        return $this->hasManyThrough('App\Models\Factory\WorkOrderItemLine', 'App\Models\Factory\WorkOrderItem');
    }

   public function line()
   {
      return $this->belongsTo('App\Models\Reference\Line');
   }

   public function workin_production_items()
   {
      return $this->work_order_items()->hasMany('App\Models\Factory\WorkinProductionItem');
   }

}
 