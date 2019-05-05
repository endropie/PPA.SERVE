<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;

class PreDelivery extends Model
{
   use Filterable;
   
   protected $fillable = [
      'number', 'customer_id', 'customer_name', 'customer_phone', 'customer_address', 'description', 
      'transaction', 'order_mode', 'rit_id', 'trans_qty', 'plan_begin_date', 'plan_until_date'
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

   public function request_order()
   {
      return $this->belongsTo('App\Models\Income\RequestOrder');
   }
}
 