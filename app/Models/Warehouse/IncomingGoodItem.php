<?php

namespace App\Models\Warehouse;

use App\Models\Model;

class IncomingGoodItem extends Model
{
   protected $fillable = [
      'item_id', 'quantity', 'unit_id', 'unit_rate'
   ];

   protected $appends = ['unit_amount'];

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

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function getUnitAmountAttribute() {

      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity * $this->unit_rate;
   }
}
 