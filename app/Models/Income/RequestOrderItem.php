<?php
namespace App\Models\Income;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestOrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'unit_id', 'unit_rate', 'quantity', 'price'
    ];

    protected $appends = ['unit_amount', 'total_delivery_order_item'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function request_order()
    {
        return $this->belongsTo('App\Models\Income\RequestOrder');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item')->with('unit');
    }

    public function stockable()
    {
        return $this->morphMany('App\Models\Common\ItemStockable', 'base');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function delivery_order_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrderItem');
    }

    public function getTotalDeliveryOrderItemAttribute() {
        return (double) $this->delivery_order_items->sum('unit_amount');
    }

    public function getUnitAmountAttribute() {
        if($this->unit_rate <= 0) return false;
        return (double) $this->quantity * $this->unit_rate;
    }
}
