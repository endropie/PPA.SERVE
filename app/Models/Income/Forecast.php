<?php

namespace App\Models\Income;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;

class Forecast extends Model
{
   use Filterable, SoftDeletes, WithUserBy, HasCommentable;

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

   public function getFullnumberAttribute()
   {
       if ($this->revise_number) return $this->number ." R.". (int) $this->revise_number;

       return $this->number;
   }
}
