<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class PackingItemOrder extends Model
{
    use SoftDeletes;

    protected $appends = ['unit_amount'];

    protected $fillable = ['work_order_item_id', 'quantity'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'quantity' => 'double',
    ];

    protected $relationships = [];

    public function packing_item()
    {
        return $this->belongsTo('App\Models\Factory\PackingItem');
    }

    public function work_order_item()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrderItem');
    }

    public function getUnitAmountAttribute() {
        // return false when rate is not valid
        $rate = $this->packing_item->unit_rate;
        return (double) ($this->quantity * $rate);
      }
}
