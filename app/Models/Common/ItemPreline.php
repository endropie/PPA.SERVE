<?php

namespace App\Models\Common;

use App\Models\Model;

class ItemPreline extends Model
{
   protected $fillable = ['line_id', 'note'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = ['incoming_good_items'];

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function line()
   {
      return $this->belongsTo('App\Models\Reference\Line');
   }
}
 