<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class OpnameStockItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'unit_id', 'unit_rate', 'stockist', 'init_amount', 'final_amount'
    ];

    protected $appends = ['quantity', 'unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'init_amount' => 'double',
        'final_amount' => 'double',
    ];

    public function opname_stock()
    {
        return $this->belongsTo('App\Models\Warehouse\OpnameStock');
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

    public function getQuantityAttribute() {
        return (double) ($this->final_amount - $this->init_amount);
    }

    public function getUnitAmountAttribute() {

        if($this->unit_rate < 0) $this->unit_rate = 0;

        return (double) ($this->quantity) * $this->unit_rate;
    }
}
