<?php

namespace App\Models\Warehouse;

use App\Models\Model;
use App\Filters\Filterable;
class IncomingGood extends Model
{
   use Filterable;

   protected $fillable = [
      'number', 'registration', 'date', 'time', 
      'customer_id', 'reference_number', 'transaction', 'order_mode', 'request_order_id',
      'transport_number', 'transport_rate', 'description', 
   ];

   protected $relationships = [
      'request_order.delivery_orders' => 'delivery_orders'
   ];

   protected $hidden = [];

   public function incoming_good_items()
   {
      return $this->hasMany('App\Models\Warehouse\IncomingGoodItem');
   }

   public function request_order() {
      return $this->belongsTo('App\Models\Income\RequestOrder');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }
}
 