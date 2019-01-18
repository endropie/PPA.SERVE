<?php

namespace App\Models\Common;

use App\Models\Model;
use App\Models\Reference\Brand;

class Item extends Model
{
   protected $fillable = ['name', 'number', 'part_mtr', 'part_fg', 'description'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = ['incoming_good_items'];

   public function brand()
   {
      return $this->belongsTo(Brand::class);
   }

   
}
 