<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class PackingItemFault extends Model
{
    use SoftDeletes;

    protected $appends = ['unit_amount'];

    protected $fillable = [
        'fault_id', 'work_order_item_id', 'quantity',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'quantity' => 'double'
    ];

    protected $relationships = [];

    public function packing_item()
    {
        return $this->belongsTo('App\Models\Factory\PackingItem');
    }

    public function fault()
    {
        return $this->belongsTo('App\Models\Reference\Fault');
    }

    public function work_order_item()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrderItem');
    }

    public function getUnitAmountAttribute() {
        $rate = $this->fresh()->packing_item ? $this->fresh()->packing_item->unit_rate : 1;
        return (double) ($this->quantity * $rate);
    }
}
