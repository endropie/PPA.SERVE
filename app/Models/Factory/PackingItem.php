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
        return (double) $this->packing_item_faults->sum('quantity') * ($this->unit_rate ?? 1);
    }

    ## return Amount of Quantity + Faulty
    public function getUnitTotalAttribute() {
        return $this->unit_amount + $this->unit_faulty;
    }

    ## Function Generate Packing item order.
    public function setPackingItemOrder($mode = null) {
        $collection = collect([]);

        foreach ($this->packing_item_orders as $packing_item_order)  {
            if ($work_order_item = $packing_item_order->work_order_item)
            {
                if ($work_order_item->work_order_packed) abort(501, "INVALID. SPK has PACKED state.");

                $packing_item_order->forceDelete();
                $work_order_item->calculate();
            }
        }

        $packing_item_orders = $this->packing_item_orders->fresh();

        $oldFinish = (double) $packing_item_orders->sum('amount_finish');
        $oldFaulty = (double) $packing_item_orders->sum('amount_faulty');

        $finish = $this->unit_amount - $oldFinish;
        $faulty = $this->unit_faulty - $oldFaulty;

        $work_order_items = WorkOrderItem::where('item_id', $this->item_id)
            ->whereRaw('amount_process > amount_packing')
            ->whereHas('work_order', function($q) use ($mode) {
                $q->whereNull('main_id');
                return ($mode == "RESET") ? $q : $q->stateHasNot('PACKED');
            })
            ->get()
            ->sortBy(function($item) {
                return $item->work_order->date ." ". $item->work_order->created_at;
            })->values();

        foreach ($work_order_items as $work_order_item) {
            $ava = (double) ($work_order_item->amount_process - $work_order_item->amount_packing);
            $total = (double) ($finish + $faulty);
            $vFinish = 0; $vFaulty = 0;

            if (round($total) <= 0) break;

            if (round($ava) >= round($total)) {
                $vFinish = $finish;
                $vFaulty = $faulty;
            }
            else if (round($ava) > round($finish)) {
                $vFinish = $finish;
                $vFaulty = $ava - $finish;
            }
            else {
                $vFinish = $ava;
                $vFaulty = 0;
            }

            $finish -= $vFinish;
            $faulty -= $vFaulty;

            $collection->push([
                'work_order_item_id' => $work_order_item->id,
                'amount_finish' => $vFinish,
                'amount_faulty' => $vFaulty,
            ]);
        }

        if ((round($finish) + round($faulty)) != 0) abort(501, "TOTAL [$finish + $faulty] PACKING ORDER INVALID");

        $created = $this->packing_item_orders()->createMany($collection->toArray());

        foreach ($created as $packing_item_order) {
            $packing_item_order->work_order_item->calculate();
        }

        return $created;
    }
}
