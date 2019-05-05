<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;
class Forecast extends Model
{
   use Filterable;

   protected $fillable = [
      'number', 'begin_date', 'until_date', 'customer_id', 'description', 
   ];

   protected $hidden = [];

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
 