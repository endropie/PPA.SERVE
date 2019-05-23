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
      'incoming_good',
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

   public function incoming_good() {
      return $this->hasOne('App\Models\Warehouse\IncomingGood');
   }

   public function getTotalDeliveryOrderItemAttribute() {
      $result = []; //collect();

      $delivery_orders = $this->delivery_orders;
      // dd($delivery_orders);
      foreach ($delivery_orders->toArray() as $delivery_order) {
         dd($delivery_order);
         foreach ($delivery_order as $detail) {
            dd($detail);
            if(!isset($result[$delivery_order->id][$detail->item_id])) $result[$delivery_order->id][$detail->item_id] = 0;
            $result[$delivery_order->id][$detail->item_id] += $detail->unit_amount;
         }
      }

      return $result;
      // return $delivery_orders->get()->map(function($c) {
      //    return $c->delivery_order_items->groupBy('item_id')->map(function($x) {
      //       return $x->sum('unit_amount');
      //    });
      // });
   }
}
 