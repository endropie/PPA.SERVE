<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;

class Packing extends Model
{
    use Filterable, SoftDeletes, WithUserBy, HasCommentable;

    protected $fillable = [
        'number', 'customer_id', 'date', 'shift_id', 'description',
        'worktime', 'begin_datetime', 'until_datetime', 'operator_id'
    ];

    protected $appends = ['fullnumber'];

    protected $hidden = ['updated_at'];

    protected $relationships = [
        'packing_items.packing_item_orders.work_order_item.work_order_closed',
        'packing_items.packing_item_orders.work_order_item.work_order_packed',
        'packing_items.packing_item_faults.work_order_item.work_order_closed',
        'packing_items.packing_item_faults.work_order_item.work_order_packed',
    ];

    public function packing_items()
    {
        return $this->hasOne('App\Models\Factory\PackingItem')->withTrashed();
    }

    public function work_order()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrder');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function operator()
    {
        return $this->belongsTo('App\Models\Common\Employee');
    }

    public function shift()
    {
        return $this->belongsTo('App\Models\Reference\Shift');
    }

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number ." R.". (int) $this->revise_number;

        return $this->number;
    }
}
