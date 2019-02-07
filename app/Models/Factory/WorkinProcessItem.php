<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkinProcessItem extends Model
{
   protected $fillable = [
      'workin_process_id', 'item_id', 'quantity'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function workin_process()
   {
      return $this->belongsTo('App\Models\Factory\WorkinProcess');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }
   
}
 