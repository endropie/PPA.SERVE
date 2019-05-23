<?php

Namespace App\Models\Common;

use App\Models\Model;

class ItemStock extends Model
{
   static $stockists = [
      'FM' => 1,
      'WO' => 2,
      'FG' => 3,
      'NG' => 4,
      'NGR' => 5,
   ];

   protected $fillable = ['item_id', 'stockist', 'total'];

   protected $appends = ['stockist_name'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = [];

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public function getStockistNameAttribute(){
      $code = $this->stockist;
      $enum = static::getStockists();
      $find = $enum->search(function ($item) use($code) { return $item == $code; });
      return $find;
   }

   public static function getStockists() {
      return collect(static::$stockists);
   }

   public static function getValidStockist($code) {
      $enum = static::getStockists();

         if(!is_integer($code)) {
            if(!$enum->has($code)) return false;
            $code = $enum->get($code);
         }
         else {
            $find = $enum->search(function ($item) use($code) { return $item == $code; });
            if(!$find) return false;
         }
         return $code;
    }
   
}
 