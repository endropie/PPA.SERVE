<?php

namespace App\Models\Income;

use App\Models\Model;

class Delivery extends Model
{
   protected $fillable = [
      'number', 'date', 'time', 'customer_id', 'customer_name', 'customer_phone', 'customer_address', 'description', 
      'rit_id', 'operator_id', 'vehicle_id', 'transport_id', 'due_date', 'due_time', 'is_revision'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function delivery_items()
   {
      return $this->hasMany('App\Models\Income\DeliveryItem');
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
 