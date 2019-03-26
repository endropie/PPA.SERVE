<?php

namespace App\Models\Income;

use App\Models\Model;

class RequestOrder extends Model
{
   protected $fillable = [
      'number', 'begin_date', 'until_date', 'customer_id', 'reference_number', 'reference_date', 
      'description', 'order_mode'
   ];

   protected $hidden = [];

   protected $model_relations = [];

   public function request_order_items()
   {
      return $this->hasMany('App\Models\Income\RequestOrderItem');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }

   public function incoming_good() {
      return $this->hasOne('App\Models\Warehouse\IncomingGood');
   }
}
 