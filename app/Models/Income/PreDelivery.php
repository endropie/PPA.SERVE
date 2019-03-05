<?php

namespace App\Models\Income;

use App\Models\Model;

class PreDelivery extends Model
{
   protected $fillable = [
      'number', 'date', 'time', 'customer_id', 'customer_name', 'customer_phone', 'customer_address', 'description', 
      'rit_id', 'trans_qty', 'plan_date', 'plan_time'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function pre_delivery_items()
   {
      return $this->hasMany('App\Models\Income\preDeliveryItem');
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
 