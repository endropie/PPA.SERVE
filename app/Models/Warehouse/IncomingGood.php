<?php

namespace App\Models\Warehouse;

use App\Models\Model;

class IncomingGood extends Model
{
   protected $fillable = [
      'number', 'date', 'time', 'customer_id', 'reference_number', 'reference_date', 
      'vehicle_id', 'tranport_rate', 'description', 
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function incoming_good_items()
   {
      return $this->hasMany('App\Models\Warehouse\IncomingGoodItem');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }
   
}
 