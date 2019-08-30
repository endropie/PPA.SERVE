<?php
namespace App\Models\Warehouse;

use App\Models\Model;
use App\Filters\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutgoingGoodVerification extends Model
{
    use Filterable, SoftDeletes;

    protected $appends = ['unit_amount'];

    protected $fillable = [
        'item_id', 'unit_id', 'unit_rate', 'quantity', 'pre_delivery_item_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [
        'outgoing_good',
    ];

    public function outgoing_good()
    {
        return $this->belongsTo('App\Models\Warehouse\OutgoingGood');
    }

    public function pre_delivery_item() {
        return $this->belongsTo('App\Models\Income\PreDeliveryItem');
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


    public function getPreDeliveryNumberAttribute() {
        // return false when rate is not valid
         if(!$this->pre_delivery_item) return null;
         if(!$this->pre_delivery_item->pre_delivery) return null;

        return $this->pre_delivery_item->pre_delivery->number;
    }

    public function getUnitAmountAttribute() {
        // return false when rate is not valid
        if($this->unit_rate <= 0) return false;

        return (double) $this->quantity * $this->unit_rate;
    }

    public function scopeWait($query) {
        return $query->whereNull('outgoing_good_id');
    }
}
