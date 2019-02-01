<?php

namespace App\Models\Common;

use App\Models\Model;

class Item extends Model
{
   protected $fillable = [
      'code', 'customer_id', 'brand_id', 'specification_id', 'part_mtr', 'part_fg',  'part_number',
      'packing_duration', 'sa_area', 'weight', 'number_hanger', 'price', 
      'category_item_id', 'type_item_id', 'size_id', 'unit_id', 'description'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = ['incoming_good_items'];

   public function item_productions()
   {
      return $this->hasMany('App\Models\Common\ItemProduction');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }

   public function brand()
   {
      return $this->belongsTo('App\Models\Reference\Brand');
   }

   public function specification()
   {
      return $this->belongsTo('App\Models\Reference\Specification');
   }
}
 