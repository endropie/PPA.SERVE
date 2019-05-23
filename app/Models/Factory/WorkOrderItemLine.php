<?php

namespace App\Models\Factory;

use App\Models\Model;

class WorkOrderItemLine extends Model
{
    protected $fillable = [
        'line_id', 'shift_id', 'begin_date', 'until_date'
    ];

    protected $appends = ['unit_amount', 'total_workin_production_item'];

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

    public function workin_production_items()
    {
        $line = (int) $this->line_id;
        return $this->hasMany('App\Models\Factory\WorkinProductionItem', 'work_order_item_id', 'work_order_item_id')
                ->whereHas('workin_production', function($q) use($line) {
                    $q->where('line_id', $line);
                });
    }

    public function getTotalWorkinProductionItemAttribute() {
       // return false when rate is not valid
       return (double) $this->workin_production_items()->sum('quantity');
    }

   public function getUnitAmountAttribute() {
    $detail = $this->belongsTo('App\Models\Factory\WorkOrderItem', 'work_order_item_id');
    
    if(!$detail) return null;

    return  $detail->first()->unit_amount;

    // ->get()->unit_amount;
 }
}
 