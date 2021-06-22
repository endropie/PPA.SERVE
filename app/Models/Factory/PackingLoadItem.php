<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class PackingLoadItem extends Model
{
    use SoftDeletes;

    protected $touches = ['packing_load'];

    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'unit_rate'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'quantity' => 'double',
        'unit_rate' => 'double',
    ];

    public function packing_load()
    {
        return $this->belongsTo('App\Models\Factory\PackingLoad');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function stockable()
    {
        return $this->morphMany('App\Models\Common\ItemStockable', 'base');
    }

    public function unit()
    {
       return $this->belongsTo('App\Models\Reference\Unit');
    }

    ## return Amount of Quantity
    public function getUnitAmountAttribute() {
        return (double) $this->quantity * ($this->unit_rate ?? 1);
    }
}
