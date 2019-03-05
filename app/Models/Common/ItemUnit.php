<?php

namespace App\Models\Common;

use App\Models\Model;

class ItemUnit extends Model
{
   protected $fillable = ['unit_id', 'rate'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }
}
 