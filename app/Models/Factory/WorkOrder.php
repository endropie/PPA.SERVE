<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkOrder extends Model
{
   protected $fillable = [
      'number', 'customer_id', 'description', 
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function work_order_items()
   {
      return $this->hasOne('App\Models\Factory\WorkOrderItem');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }
   
}
 