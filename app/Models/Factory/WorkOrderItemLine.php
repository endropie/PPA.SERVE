<?php

namespace App\Models\Factory;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderItemLine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'line_id', 'shift_id', 'ismain'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function work_order_item()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrderItem')->withTrashed();
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Reference\Line');
    }

    public function work_production_items()
    {
        return $this->hasMany('App\Models\Factory\WorkProductionItem');
    }

    public function getUnitAmountAttribute() {
        return  (double) $this->work_order_item->quantity * $this->work_order_item->unit_rate;
    }

    public function calculate () {
        $total = $this->work_production_items->sum('unit_amount');
        $this->amount_line = $total;
        $this->save();
    }
}
