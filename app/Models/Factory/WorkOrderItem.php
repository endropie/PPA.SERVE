<?php

namespace App\Models\Factory;

use App\Filters\Filterable;
use App\Models\Model;
use App\Traits\HasCommentable;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderItem extends Model
{
    use Filterable, SoftDeletes, HasCommentable;

    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'target', 'unit_rate', 'ngratio'
    ];

    protected $touches = ['work_order'];

    protected $appends = ['unit_amount', 'work_order_number'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double',
        'target' => 'double',
        'ngratio' => 'double',
        'amount_process' => 'double',
        'amount_packing' => 'double',
        'amount_faulty' => 'double',
    ];

    protected $relationships = [];

    public function packing_items()
    {
        return $this->belongsToMany('App\Models\Factory\PackingItem', 'packing_item_orders');
    }

    public function packing_item_orders()
    {
        return $this->hasMany('App\Models\Factory\PackingItemOrder');
    }

    public function packing_item_faults()
    {
        return $this->hasMany('App\Models\Factory\PackingItemFault');
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
        if(!$this->fresh()->item->load_capacity) return null;
        return (double) $this->unit_amount / $this->fresh()->item->load_capacity;
    }

    public function getHangerProductionAttribute()
    {
        if(!$this->fresh()->item->load_capacity) return null;
        return (double) $this->amount_process / $this->fresh()->item->load_capacity;
    }

    public function getHangerPackingAttribute()
    {
        if(!$this->fresh()->item->load_capacity) return null;
        return (double) $this->amount_packing / $this->fresh()->item->load_capacity;
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

    public function getWorkOrderNumberAttribute()
    {
        $work_order = $this->fresh()->work_order;
        return  $work_order->fullnumber ?? null;
    }

    public function getWorkOrderDateAttribute()
    {
        $work_order = $this->fresh()->work_order;
        return  $work_order->date ?? null;
    }

    public function getWorkOrderShiftAttribute()
    {
        $work_order = $this->fresh()->work_order;
        return  $work_order->shift->name ?? null;
    }

    public function calculate($error = true)
    {

        $process = (double) $this->fresh()->work_production_items->sum('unit_amount');
        $packing = (double) $this->fresh()->packing_item_orders->sum('unit_amount');
        $faulty = (double) $this->fresh()->packing_item_faults->sum('unit_amount');

        $this->amount_process = $process;
        $this->amount_packing = $packing;
        $this->amount_faulty = $faulty;
        $this->save();

        if($error && round($this->unit_amount) < round($this->amount_process)) {
            abort(501, "AMOUNT PROCESS [#". $this->id ."] INVALID");
        }

        if($error && round($this->amount_process) < round($this->amount_packing + $this->amount_faulty)) {
            abort(501, "AMOUNT PACKING+FAULTY [#". $this->id ."] INVALID");
        }
    }
}
