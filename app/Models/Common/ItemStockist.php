<?php

Namespace App\Models\Common;

use App\Extension\Enum\Enum;

final class ItemStockist extends Enum
{
    const FM = 1;
    const WO = 2;
    const FG = 3;
    const NG = 4;
    const NGR = 5;

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