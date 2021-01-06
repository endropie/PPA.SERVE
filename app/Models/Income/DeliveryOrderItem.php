<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrderItem extends Model
{
    use Filterable, SoftDeletes;

    protected $fillable = [
        'item_id', 'unit_id', 'unit_rate', 'quantity', 'encasement'
    ];

    protected $appends = ['unit_amount', 'number_lots'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double',
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
        return $this->belongsTo('App\Models\Common\Item')->withSampled();
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

    public function getNumberLotsAttribute() {
        if ($id = $this->getAttribute('request_order_item_id')) {
            if($request_order_item = RequestOrderItem::find($id)) {
                if ($request_order_item->incoming_good_item) return $request_order_item->incoming_good_item->lots ?? '-';
            }
        }
        return null;
    }
}
