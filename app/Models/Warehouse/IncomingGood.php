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
      'incoming_good_items.request_order_item.request_order.delivery_orders' => 'delivery_orders',
      'incoming_good_items.request_order_item.pre_delivery_items' => 'pre_delivery_items',
   ];

   protected $hidden = [];

   public function incoming_good_items()
   {
      return $this->hasMany('App\Models\Warehouse\IncomingGoodItem');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }

   public function request_order() {
      return $this->hasOne('App\Models\Income\RequestOrder');
   }
   
}
 