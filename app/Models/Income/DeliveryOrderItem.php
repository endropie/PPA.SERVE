<?php

namespace App\Models\Income;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'unit_id', 'unit_rate', 'quantity', 'encasement'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double'
    ];

    public function delivery_order()
    {
        return $this->belongsTo('App\Models\Income\DeliveryOrder');
    }

    public function request_order_item()
    {
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

    public function getTotalRequestOrderItemAttribute() {
        return (double) $this->request_order_item->unit_amount;
    }

    public function getUnitAmountAttribute() {
        return (double) $this->quantity * $this->unit_rate;
    }
}
