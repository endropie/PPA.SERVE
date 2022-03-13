<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Models\WithStateable;
use App\Filters\Filterable;
use App\Traits\HasCommentable;

class WorkOrder extends Model
{
    use Filterable, SoftDeletes, WithUserBy, WithStateable, HasCommentable;

    protected $fillable = [
        'number', 'line_id', 'date', 'shift_id', 'stockist_from', 'stockist_direct', 'mode_line', 'description', 'main_id'
    ];

    protected $appends = ['fullnumber'];

    protected $relationships = [
        'work_order_items.packing_item_orders',
        'work_order_items.work_production_items',
        'sub_work_orders.work_order_items.work_production_items',
    ];

    protected $hidden = ['updated_at'];

    public function work_order_items() {
        return $this->hasMany('App\Models\Factory\WorkOrderItem')->withTrashed();
    }

    public function sub_work_orders() {
        return $this->hasMany('App\Models\Factory\WorkOrder', 'main_id');
    }

    public function work_production_items() {
        return $this->hasManyThrough('App\Models\Factory\WorkProductionItem', 'App\Models\Factory\WorkProduction');
    }

    public function line() {
        return $this->belongsTo('App\Models\Reference\Line');
    }

    public function shift() {
        return $this->belongsTo('App\Models\Reference\Shift');
    }

    public function getSummaryItemsAttribute() {
        return (double) $this->fresh()->work_order_items->sum('quantity');
    }

    public function getSummaryProductionsAttribute() {
        return (double) $this->fresh()->work_order_items->sum(function($item) {
            return (double) ($item->amount_process / ($item->unit_rate?? 1));
        });
    }

    public function getSummaryPackingsAttribute() {
        return (double) $this->fresh()->work_order_items->sum(function($item) {
            return (double) ($item->amount_packing / ($item->unit_rate?? 1));
        });
    }

    public function getTotalAmountAttribute() {
        return (double) $this->fresh()->work_order_items->sum('unit_amount');
    }

    public function getTotalProductionAttribute() {
        return (double) $this->fresh()->work_order_items->sum('amount_process');
    }

    public function getTotalPackingAttribute() {
        return (double) $this->fresh()->work_order_items->sum('amount_packing');
    }

    public function getHangerAmountAttribute() {
        return (double) $this->fresh()->work_order_items->sum('hanger_amount');
    }

    public function getHangerProductionAttribute() {

        return (double) $this->fresh()->work_order_items->sum('hanger_production');
    }

    public function getHangerPackingAttribute() {
        return (double) $this->fresh()->work_order_items->sum('hanger_packing');
    }

    public function getHasProductedAttribute() {
        $row = $this->fresh()->stateable->where('state', 'PRODUCTED')->last();
        return $row ?? null;
    }

    public function getHasPackedAttribute() {
        $row = $this->fresh()->stateable->where('state', 'PACKED')->last();
        return $row ?? null;
    }

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number ." R.". (int) $this->revise_number;

        return $this->number;
    }
}
