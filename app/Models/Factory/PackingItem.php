<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class PackingItem extends Model
{
    use SoftDeletes;

    protected $touches = ['packing'];

    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'unit_rate', 'type_fault_id'
    ];

    protected $appends = ['unit_amount', 'unit_total'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double',
        'faulty' => 'double',
    ];

    public function packing_item_faults()
    {
        return $this->hasMany('App\Models\Factory\PackingItemFault')->withTrashed();
    }

    public function packing_item_orders()
    {
        return $this->hasMany('App\Models\Factory\PackingItemOrder')->withTrashed();
    }

    public function packing()
    {
        return $this->belongsTo('App\Models\Factory\Packing');
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

    ## return Amount of Faulty
    public function getUnitFaultyAttribute() {
        return (double) $this->faulty * ($this->unit_rate ?? 1);
    }

    ## return Amount of Quantity + Faulty
    public function getUnitTotalAttribute() {
        return $this->unit_amount + $this->unit_faulty;
    }
}
