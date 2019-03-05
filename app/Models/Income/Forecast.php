<?php

namespace App\Models\Income;

use App\Models\Model;

class Forecast extends Model
{
   protected $fillable = [
      'number', 'start_date', 'end_date', 'customer_id', 'description', 
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function forecast_items()
   {
      return $this->hasMany('App\Models\Income\ForecastItem');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }
}
 