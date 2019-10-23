<?php

namespace App\Models\Warehouse;

use App\Models\Model;
use App\Filters\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutgoingGoodItem extends Model
{
    use Filterable, SoftDeletes;

    protected $appends = ['unit_amount'];

    protected $fillable = [
        'item_id', 'unit_id', 'unit_rate', 'quantity',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double'
    ];

    protected $relationships = [
        'outgoing_good' => 'outgoing_good',
    ];

    public function outgoing_good()
    {
        return $this->belongsTo('App\Models\Warehouse\OutgoingGood');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function stockable()
    {
        return $this->morphMany('App\Models\Common\ItemStockable', 'base');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getUnitAmountAttribute() {
        // return false when rate is not valid
        if($this->unit_rate <= 0) return false;

        return (double) $this->quantity * $this->unit_rate;
    }
}
