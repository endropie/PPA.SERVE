<?php

namespace App\Models\Common;

use App\Models\Model;

class ItemStock extends Model
{
    static $stockists = [
        'FM' => 'Fresh',
        'WIP' => 'Work In Process',
        'PFG' => 'Pre-Finish Good',
        'FG' => 'Finish Good',
        'NC' => 'No-Common',
        'NCR' => 'No-Common Return',
        'NG' => 'No-Good'
    ];

    protected $fillable = ['item_id', 'stockist', 'total'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'total' => 'double'
    ];

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public static function getStockists()
    {
        return collect(static::$stockists);
    }

    public static function getValidStockist($code)
    {
        $enum = static::getStockists();
        if (!$enum->has($code)) {
            abort(500, 'CODE STOCK INVALID!');
        }
        return $code;
    }
}
