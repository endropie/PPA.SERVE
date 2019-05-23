<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;

class DeliveryOrder extends Model
{
   use Filterable;

   protected $fillable = [
      'number', 'numrev', 'customer_id', 'customer_name', 'customer_phone', 'customer_address', 'description', 
      'transaction', 'date', 'time', 'due_date', 'due_time', 'operator_id', 'transport_number', 'transport_rate',
      // 'ship_delivery_id',
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = [];

   public function delivery_order_items()
   {
      return $this->hasMany('App\Models\Income\DeliveryOrderItem');
   }

   public function ship_delivery()
   {
      return $this->hasMany('App\Models\Income\ShipDelivery');
   }

   public function request_order()
   {
      return $this->belongsTo('App\Models\Income\RequestOrder');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }

   public function vehicle()
   {
      return $this->belongsTo('App\Models\Reference\Vehicle');
   }

   public function operator()
   {
      return $this->belongsTo('App\Models\Reference\Operator');
   }

   public function getHasRevisionAttribute()
   {
      return $this->hasMany(get_class($this),'id')->where('number', $this->number)->where('id', '!=', $this->id);
   }
}
 