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
        'amount_reconcile' => 'double',
    ];

    public function delivery_order()
    {
        return $this->belongsTo('App\Models\Income\DeliveryOrder');
    }

    public function reconcile_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrderItem', 'reconcile_item_id');
    }

    public function reconcile_item()
    {
        return $this->belongsTo('App\Models\Income\DeliveryOrderItem');
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

    public function calculate() {
        if ($this->delivery_order->is_internal) {
            $sum = (double) $this->reconcile_items->sum('unit_amount');
            $this->amount_reconcile = $sum;
            $this->save();
        }
    }

    public function getReconcileItem($reconcile) {
        if ($reconcile) {
            foreach ($reconcile->delivery_order_items as $detail) {
                $dif = (double) ($detail->unit_amount - $detail->amount_reconcile);
                if ($detail->item_id == $this->item_id && $dif > 0) {
                    if ($this->unit_amount > $dif)  abort(501, 'TOTAL UNIT FAILED');
                    return $detail;
                }
            }
        }

        return null;
    }
}
