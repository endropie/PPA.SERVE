<?php

namespace App\Models\Warehouse;

use App\Models\Model;
use App\Filters\Filterable;
class IncomingGood extends Model
{
   use Filterable;

   protected $fillable = [
      'number', 'registration', 'date', 'time', 
      'customer_id', 'reference_number', 'reference_date', 'transaction', 'order_mode', 'request_order_id',
      'transport_number', 'transport_rate', 'description', 
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
 