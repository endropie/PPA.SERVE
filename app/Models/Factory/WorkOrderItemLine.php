<?php

namespace App\Models\Factory;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderItemLine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'line_id', 'shift_id'
    ];

    protected $appends = [];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function work_order_item()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrderItem');
    }

    public function line()
    {
        return $this->belongsTo('App\Models\Reference\Line');
    }

    public function production_items()
    {
        $line = (int) $this->line_id;
        return $this->hasMany('App\Models\Factory\ProductionItem', 'work_order_item_id', 'work_order_item_id')
                ->whereHas('production', function($q) use($line) {
                    $q->where('line_id', $line);
                });
    }

    public function getTotalProductionItemAttribute() {
       // return false when rate is not valid
       return (double) $this->production_items()->sum('quantity');
    }

   public function getUnitAmountAttribute() {
    return  (double) $this->quantity * $this->work_order_item->unit_rate;

 }
}
