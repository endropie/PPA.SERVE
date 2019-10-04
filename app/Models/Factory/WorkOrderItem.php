<?php

namespace App\Models\Factory;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use function Safe\json_encode;

class WorkOrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'target', 'unit_rate', 'ngratio'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function work_order_item_lines()
    {
        return $this->hasMany('App\Models\Factory\WorkOrderItemLine')->withTrashed();
    }

    public function work_order()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrder')->withTrashed();
    }

    public function work_order_closed()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrder', 'work_order_id')->where('status', 'CLOSED');
    }

    public function packing_items()
    {
        return $this->hasMany('App\Models\Factory\PackingItem');
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

    public function work_production_items()
    {
        return $this->hasMany('App\Models\Factory\WorkProductionItem');
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
            $total = (double) collect($this->work_order_item_lines->map(function($line) {
                return (double) $line->work_production_items->sum('unit_amount');
            }))->sum();

            $this->amount_process = $total * $this->unit_rate;
            $this->save();

            if(round($this->unit_amount) < round($this->amount_process)) {
                abort(501, "AMOUNT PROCESS [#". $this->id ."] INVALID");
            }
        }
        if(!$param || $param == 'packing') {
            // UPDATE AMOUNT PACKING
            $this->amount_packing = $this->packing_items->sum('unit_amount') + $this->packing_items->sum('amount_faulty');
            // abort(501, 'PACKING: unit_amount->'. $this->packing_items->sum('unit_amount') .' amount_foulty->'.$this->packing_items->sum('amount_faulty'));
            $this->save();

            if(round($this->unit_amount) < round($this->amount_packing)) {
                abort(501, "AMOUNT PACKING [#". $this->id ."] INVALID");
            }
        }
    }
}
