<?php

namespace App\Models\Warehouse;

use App\Models\Model;

class IncomingGoodItem extends Model
{
   protected $fillable = [
      'incoming_good_id', 'item_id', 'quantity'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function incoming_good()
   {
      return $this->belongsTo('App\Models\Factory\IncomingGood');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }
   
}
 