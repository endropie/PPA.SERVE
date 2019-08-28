<?php

Namespace App\Models\Common;

use App\Models\Model;

class ItemStock extends Model
{
   static $stockists = [
      'FM' => 'Fresh Material',
      'WO' => 'Work Order',
      'WIP' => 'Work In Process',
      'FG' => 'Finish Good',
      'NG' => 'Not Good',
      'RET' => 'Not Good Return',
      // 'RDO' => 'Request-DO amounable',
      // 'PDO' => 'Pre-DO amounable',
      'VDO' => 'Verification-DO amounable',
      'PDO.REG' => 'REGULER-PDO amounable', // REGDO
      'PDO.RET' => 'RETURN-PDO amounable', // RETDO
      'RDO.REG' => 'REGULER Request-DO amounable',
      'RDO.RET' => 'RETURN Request-DO amounable',
   ];

   protected $fillable = ['item_id', 'stockist', 'total'];

   protected $appends = [
      // 'stockist_name'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   public function item()
   {
      return $this->belongsTo('App\Models\Common\Item');
   }

   public static function getStockists() {
      return collect(static::$stockists);
   }

   public static function getValidStockist($code) {
      $enum = static::getStockists();
      if(!$enum->has($code)) {
         abort(500, 'CODE STOCK INVALID!');
      }
      return $code;
    }

   public static function XXgetValidStockist($code) {
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