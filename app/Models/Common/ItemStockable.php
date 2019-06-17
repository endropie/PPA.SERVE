<?php

namespace App\Models\Common;

use App\Models\Model;

class ItemStockable extends Model
{
   protected $fillable = ['item_id', 'stockist', 'unit_amount', 'base_type', 'base_id'];

   protected $hidden = ['created_at', 'updated_at'];

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function base()
   {
      return $this->morphTo();
   }
}
 