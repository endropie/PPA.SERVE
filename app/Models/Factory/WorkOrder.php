<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Filters\Filterable;

class WorkOrder extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'line_id', 'date', 'shift_id', 'stockist_from', 'description',
    ];

    protected $relationships = [
        'work_order_items.packing_items',
        'work_order_items.work_order_item_lines.work_production_items',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function work_order_items() {
        return $this->hasMany('App\Models\Factory\WorkOrderItem')->withTrashed();
    }

    public function work_order_item_lines() {
        return $this->hasManyThrough('App\Models\Factory\WorkOrderItemLine', 'App\Models\Factory\WorkOrderItem')
                    ->withTrashed();
    }

    public function line() {
        return $this->belongsTo('App\Models\Reference\Line');
    }

    public function shift() {
        return $this->belongsTo('App\Models\Reference\Shift');
    }

    public function getStatusProductionAttribute() {
        $get = $this->hasMany('App\Models\Factory\WorkOrderItem')->get();
        $total = $get->sum(function ($detail) {
            return $detail['quantity'] * $detail['unit_rate'];
        });
        $amount  = $get->sum('amount_process');
        return round($total) != round($amount)
            ? (int) ($amount/$total * 100)
            : (bool) ($amount != 0) ;
    }

    public function getStatusPackingAttribute() {
        $get = $this->hasMany('App\Models\Factory\WorkOrderItem')->get();
        $total = $get->sum(function ($detail) {
            return $detail['quantity'] * $detail['unit_rate'];
        });
        $amount  = $get->sum('amount_packing');
        return round($total) != round($amount)
            ? (int) ($amount/$total * 100)
            : (bool) ($amount != 0);
    }
}
