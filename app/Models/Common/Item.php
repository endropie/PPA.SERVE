<?php

namespace App\Models\Common;

use App\Models\Model;

class Item extends Model
{
   protected $fillable = [
      'code', 'customer_id', 'brand_id', 'specification_id', 'part_mtr', 'part_fg',  'part_number', 'order_number',
      'times_packing', 'sa_area', 'weight', 'price', 'marketplace_id', 'ordertype_id', 'size_id', 'unit_id', 'description'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = ['incoming_good_items'];

   public function brand()
   {
      return $this->belongsTo('App\Models\Reference\Brand');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }

   public function specification()
   {
      return $this->belongsTo('App\Models\Common\Specification');
   }   
}
 