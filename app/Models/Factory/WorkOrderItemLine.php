<?php

namespace App\Models\Factory;

use App\Filters\Filterable;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderItemLine extends Model
{
    use Filterable, SoftDeletes;

    protected $fillable = [
        'line_id', 'shift_id', 'ismain'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

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

        $this->amount_line = (double) $this->work_production_items->sum('unit_amount');

        if($this->amount_line > $this->unit_amount) {
            abort(501, "AMOUNT TOTAL [#". $this->id ."] INVALID");
        }
        $this->save();
    }
}
