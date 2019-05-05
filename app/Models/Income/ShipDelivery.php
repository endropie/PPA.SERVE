<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;

class ShipDelivery extends Model
{
   use Filterable;

   protected $fillable = [
      'number', 'customer_id', 'customer_name', 'customer_phone', 'customer_address', 'description', 
      'pre_delivery_id',
      'transaction', 'ship_date', 'ship_time', 'due_date', 'due_time', 'rit_id', 'operator_id', 'vehicle_id', 'transport_id', 'is_revision'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function ship_delivery_items()
   {
      return $this->hasMany('App\Models\Income\ShipDeliveryItem');
   }

   public function delivery_orders()
   {
      return $this->hasMany('App\Models\Income\DeliveryOrder');
   }

   public function pre_delivery()
   {
      return $this->belongsTo('App\Models\Income\PreDelivery');
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
}
 