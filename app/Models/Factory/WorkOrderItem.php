<?php

namespace App\Models\Factory;

use App\Filters\Filterable;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderItem extends Model
{
    use Filterable, SoftDeletes;

    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'target', 'unit_rate', 'ngratio'
    ];

    protected $touches = ['work_order'];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double',
        'target' => 'double',
        'ngratio' => 'double',
        'amount_process' => 'double',
        'amount_packing' => 'double',
    ];

    protected $relationships = [];

    public function work_order_item_lines()
    {
        return $this->hasMany('App\Models\Factory\WorkOrderItemLine')->withTrashed();
    }

    public function packing_items()
    {
        return $this->belongsToMany('App\Models\Factory\PackingItem', 'packing_item_orders');
    }

    public function packing_item_orders()
    {
        return $this->hasMany('App\Models\Factory\PackingItemOrder');
    }

    public function work_order()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrder')->withTrashed();
    }

    public function work_order_producted()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrder', 'work_order_id')->whereHas('stateable', function($query) {
            $query->where('state', 'PRODUCTED');
        });
    }

    public function work_order_packed()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrder', 'work_order_id')->whereHas('stateable', function($query) {
            $query->where('state', 'PACKED');
        });
    }

    public function work_order_closed()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrder', 'work_order_id')->where('status', 'CLOSED');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function stockable()
    {
        return $this->morphMany('App\Models\Common\ItemStockable', 'base');
    }

    public function work_production_items()
    {
        return $this->hasMany('App\Models\Factory\WorkProductionItem');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getHangerAmountAttribute()
    {
        if(!$this->item->load_capacity) return null;
        return (double) $this->unit_amount / $this->item->load_capacity;
    }

    public function getHangerProductionAttribute()
    {
        if(!$this->item->load_capacity) return null;
        return (double) $this->amount_process / $this->item->load_capacity;
    }

    public function getHangerPackingAttribute()
    {
        if(!$this->item->load_capacity) return null;
        return (double) $this->amount_packing / $this->item->load_capacity;
    }

    public function getUnitAmountAttribute()
    {
        // return false when rate is not valid
        if($this->unit_rate <= 0) return null;

        return (double) $this->quantity * $this->unit_rate;
    }

    public function getUnitProcessAttribute()
    {
        // return false when rate is not valid
        if($this->unit_rate <= 0) return null;

        return (double) $this->process * $this->unit_rate;
    }

    public function calculate($param = null)
    {
        if(!$param || $param == 'process') {
            // UPDATE AMOUNT PACKING
            $total = (double) collect(
                $this->work_order_item_lines
                  ->filter(function($line) { return (boolean) $line->ismain; })
                  ->map(function($line) {
                    return (double) $line->work_production_items->sum('unit_amount');
                  })
            )->sum();

            $this->amount_process = $total;
            $this->save();

            if(round($this->unit_amount) < round($this->amount_process)) {
                abort(501, "AMOUNT PROCESS [#". $this->id ."] INVALID");
            }
        }
        if(!$param || $param == 'packing') {

            ## UPDATE AMOUNT PACKING
            $finsih = (double) $this->packing_item_orders->sum('amount_finish');
            $faulty = (double) $this->packing_item_orders->sum('amount_faulty');

            $this->amount_packing = $finsih + $faulty;
            $this->save();

            if(round($this->amount_process) < round($this->amount_packing)) {
                abort(501, "AMOUNT PACKING [#". $this->id ."] INVALID");
                abort(501, "PROCESS [$this->unit_process] < PACKING [$this->amount_packing");
            }
        }
    }
}
