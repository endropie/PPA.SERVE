<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;

class RequestOrder extends Model
{
   use Filterable;
   
   protected $fillable = [
      'number', 'date', 'begin_date', 'until_date', 'customer_id', 'reference_number',
      'description', 'order_mode'
   ];
 
   protected $relationships = [
      'incoming_goods',
      'delivery_orders'
   ];

   public function request_order_items()
   {
      return $this->hasMany('App\Models\Income\RequestOrderItem');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }

   public function delivery_orders() {
      return $this->hasMany('App\Models\Income\DeliveryOrder');
   }

   public function incoming_goods() {
      return $this->hasMany('App\Models\Warehouse\IncomingGood');
   }
}
 