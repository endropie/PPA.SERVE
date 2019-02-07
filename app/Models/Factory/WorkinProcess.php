<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkinProcess extends Model
{
   protected $fillable = [
      'number', 'start_date', 'start_time', 'end_date', 'end_time', 'customer_id', 'description', 
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function workin_process_items()
   {
      return $this->hasMany('App\Models\Factory\WorkinProcessItem');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }
   
}
 