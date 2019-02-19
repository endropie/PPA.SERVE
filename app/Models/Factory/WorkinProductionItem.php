<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkinProductionItem extends Model
{
   protected $fillable = [
      'item_id', 'quantity',
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function workin_production()
   {
      return $this->belongsTo('App\Models\Factory\WorkinProduction');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }
   
}
 