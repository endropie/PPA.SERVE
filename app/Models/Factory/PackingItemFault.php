<?php

namespace App\Models\Factory;

use App\Models\Model;

class PackingItemFault extends Model
{
   protected $fillable = [
      'fault_id', 'quantity',
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function packing_item()
   {
      return $this->belongsTo('App\Models\Factory\PackingItem');
   }

   public function fault()
   {
      return $this->belongsTo('App\Models\Reference\Fault');
   }

}
 