<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class IncomingGoodItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'unit_rate', 'valid', 'note'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function incoming_good()
    {
        return $this->belongsTo('App\Models\Factory\IncomingGood');
    }

    public function request_order_item() {
        return $this->belongsTo('App\Models\Income\RequestOrderItem');
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

    public function getUnitAmountAttribute() {

        // return false when rate is not valid
        if($this->unit_rate <= 0) return false;

        return (double) $this->quantity * $this->unit_rate;
    }

    public function getUnitValidAttribute() {

        // return false when rate is not valid
        if($this->unit_rate <= 0) return false;

        return (double) $this->valid * $this->unit_rate;
    }
}
