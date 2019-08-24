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
        // 'delivery_orders'
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
}
