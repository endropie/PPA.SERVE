<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkinProduction extends Model
{
   protected $fillable = ['number', 'line_id', 'date', 'shift_id', 'type_worktime_id', 'description'];

   protected $hidden = ['created_at', 'updated_at'];

   public function workin_production_items()
   {
      return $this->hasMany('App\Models\Factory\WorkinProductionItem');
   }

   public function line()
   {
      return $this->belongsTo('App\Models\Reference\Line');
   }

   public function shift()
   {
      return $this->belongsTo('App\Models\Reference\Shift');
   }

   public function type_worktime()
   {
      return $this->belongsTo('App\Models\Reference\TypeWorktime');
   }
}
