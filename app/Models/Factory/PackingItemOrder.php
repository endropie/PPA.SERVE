<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class PackingItemOrder extends Model
{
    use SoftDeletes;

    protected $appends = ['unit_total'];

    protected $fillable = ['work_order_item_id', 'amount_finish', 'amount_faulty'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'amount_finish' => 'double',
        'amount_faulty' => 'double'
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

    public function getUnitTotalAttribute() {
        // return false when rate is not valid
        $total = (double) ($this->amount_finish + $this->amount_faulty);
        return $total;
      }
}
