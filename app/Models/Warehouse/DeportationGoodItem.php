<?php

namespace App\Models\Warehouse;

use App\Filters\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class DeportationGoodItem extends Model
{
    use Filterable, SoftDeletes;

    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'unit_rate', 'stockist_from'
    ];

    protected $appends = ['unit_amount'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double'
    ];

    protected $relationships = [];

    public function deportation_good()
    {
        return $this->belongsTo('App\Models\Warehouse\IncomingGood');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item')->withSampled();
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
