<?php

Namespace App\Models\Common;

use App\Extension\Enum\Enum;

final class ItemStockist extends Enum
{
    const incoming_good = 1;
    const work_order  = 2;
    const packing_item = 3;
    const delivery = 4;

    public static function getValidStockist($stockist) {
        if(!is_integer($stockist)) {
            if(!static::hasKey($stockist)) return false;
            $stockist = static::getValue($stockist);
         }
         else {
            if(!static::hasValue($stockist)) return false;
         }

         return $stockist;
    }
}