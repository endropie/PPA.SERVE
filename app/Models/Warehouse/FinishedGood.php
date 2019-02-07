<?php

namespace App\Models\Warehouse;

use App\Models\Model;

class FinishedGood extends Model
{
   protected $fillable = [
      'number', 'date', 'time', 'customer_id', 'description', 
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function finished_good_items()
   {
      return $this->hasMany('App\Models\Warehouse\FinishedGoodItem');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }
   
}
 