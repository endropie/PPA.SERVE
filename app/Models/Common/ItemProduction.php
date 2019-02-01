<?php

namespace App\Models\Common;

use App\Models\Model;

class ItemProduction extends Model
{
   protected $fillable = ['production_id', 'reference'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = ['incoming_good_items'];

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function production()
   {
      return $this->belongsTo('App\Models\Factory\Production');
   }
}
 