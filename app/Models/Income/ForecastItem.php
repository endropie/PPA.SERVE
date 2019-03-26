<?php

namespace App\Models\Income;

use App\Models\Model;

class ForecastItem extends Model
{
   protected $fillable = [
      'item_id', 'unit_id', 'unit_rate', 'quantity', 'price', 'note'
   ];

   protected $appends = ['unit_stock'];
   
   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function forecast()
   {
      return $this->belongsTo('App\Models\Income\Forecast');
   }

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item')->with('unit');
   }

   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function getUnitStockAttribute() {

      // return false when rate is not valid
      if($this->unit_rate <= 0) return false;
      
      return (double) $this->quantity / $this->unit_rate;
   }
}
 