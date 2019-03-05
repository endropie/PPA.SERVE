<?php

Namespace App\Models\Common;

use App\Models\Model;

class ItemStock extends Model
{
   protected $fillable = ['item_id', 'stockist', 'total'];

   protected $appends = ['stockist_name'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function getStockistNameAttribute()
   {
      return ItemStockist::getKey($this->stockist);
   }
   
}
 