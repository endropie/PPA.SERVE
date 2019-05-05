<?php

namespace App\Models\Income;

use App\Models\Model;

class RequestOrderItem extends Model
{
   protected $fillable = [
      'item_id', 'unit_id', 'unit_rate', 'quantity', 'price'
   ];

   protected $appends = ['unit_amount', 'total_mount_pre_delivery_item', 'total_mount_delivery_order_item'];
   
   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function request_order()
   {
      return $this->belongsTo('App\Models\Income\RequestOrder');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item')->with('unit');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function base_extractables() // Type of Morph is "base" or "mount"
   {
      return $this->morphMany('App\Models\Common\ItemExtractable', 'base');
   }

   public function sum_unit_mount($mount_type) {
      // $mount_type = 'App\Models\Income\PreDeliveryItem';
      return (double) $this->morphMany('App\Models\Common\ItemExtractable', 'base')
         ->where('mount_type', $mount_type)->sum('unit_amount');
   }

   public function getTotalMountPreDeliveryItemAttribute() {
      // return false when rate is not valid
      $mount_type = 'App\Models\Income\PreDeliveryItem';
      $details = $this->morphMany('App\Models\Common\ItemExtractable', 'base')
         ->where('mount_type', $mount_type);
         // ->sum('unit_amount');
      return (double) $details->get()->sum(function($c) {
         return $c->mount->unit_amount;
      });
   }

   public function getTotalMountDeliveryOrderItemAttribute() {
      // return false when rate is not valid
      $mount_type = 'App\Models\Income\DeliveryOrderItem';
      $details = $this->morphMany('App\Models\Common\ItemExtractable', 'base')
         ->where('mount_type', $mount_type);
         // ->sum('unit_amount');
      return (double) $details->get()->sum(function($c) {
         return $c->mount->unit_amount;
      });
   }

   public function getUnitAmountAttribute() {
      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
   }
}
 