<?php

namespace App\Models\Factory;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkProductionItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'unit_rate', 'stockist'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double'
    ];

    protected $relationships = [];

    public function work_production()
    {
        return $this->belongsTo('App\Models\Factory\WorkProduction');
    }

    public function work_order_item()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrderItem');
    }

    public function stockable()
    {
        return $this->morphMany('App\Models\Common\ItemStockable', 'base');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getUnitAmountAttribute()
    {
        // return false when rate is not valid
        if($this->unit_rate <= 0) return null;

        return (double) $this->quantity * $this->unit_rate;
    }

}
