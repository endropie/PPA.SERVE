<?php

namespace App\Models\Income;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Filters\Filterable;

class Forecast extends Model
{
   use Filterable, SoftDeletes;

   protected $fillable = [
      'number', 'begin_date', 'until_date', 'customer_id', 'description',
   ];

   protected $hidden = [];

   protected $relationships = [];

   public function forecast_items()
   {
      return $this->hasMany('App\Models\Income\ForecastItem')->withTrashed();
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }
}
