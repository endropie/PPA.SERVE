<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;

class ShipDelivery extends Model
{
   use Filterable;

   protected $fillable = [
      'number', 'customer_id', 'customer_name', 'customer_phone', 'customer_address', 'description',
      'transaction', 'date', 'time', 'due_date', 'due_time', 'operator_id', 'transport_number', 'transport_rate'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = [
      'revise_delivery_orders' => 'delivery_orders.revise'
   ];

   public function ship_delivery_items()
   {
      return $this->hasMany('App\Models\Income\ShipDeliveryItem');
   }

   public function delivery_orders()
   {
      return $this->hasMany('App\Models\Income\DeliveryOrder');
   }

   public function revise_delivery_orders()
   {
      return $this->hasMany('App\Models\Income\DeliveryOrder')->whereNotNull('revise_id');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }

   public function operator()
   {
      return $this->belongsTo('App\Models\Reference\Operator');
   }
}
 