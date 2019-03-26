<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkOrder extends Model
{
   protected $fillable = [
      'number', 'customer_id', 'description', 
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_mandatories = ['work_order_items'];
   protected $model_depedencies = ['workin_production_items'];

   public function work_order_items()
   {
      return $this->hasOne('App\Models\Factory\WorkOrderItem');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }

   public function workin_production_items()
   {
      return $this->work_order_items->hasMany('App\Models\Factory\WorkinProductionItem');
   }

}
 