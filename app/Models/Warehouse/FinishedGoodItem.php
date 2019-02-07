<?php

namespace App\Models\Warehouse;

use App\Models\Model;

class FinishedGoodItem extends Model
{
   protected $fillable = [
      'finished_good_id', 'item_id', 'quantity'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function finished_good()
   {
      return $this->belongsTo('App\Models\Factory\FinishedGood');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }
   
}
 