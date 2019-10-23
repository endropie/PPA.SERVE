<?php

namespace App\Models\Common;

use App\Filters\Filterable;
use App\Models\Model;

class ItemStockable extends Model
{
    use Filterable;

    protected $fillable = ['item_id', 'stockist', 'unit_amount', 'base_type', 'base_id'];

    // protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_amount' => 'double'
    ];

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function base()
    {
        return $this->morphTo();
    }

    public function getBaseLabelAttribute() {
        switch ($this->base_type) {
            case 'App\Models\Warehouse\IncomingGoodItem':
            return $this->base->incoming_good->number;

            case 'App\Models\Income\RequestOrderItem':
            return $this->base->request_order->number;

            case 'App\Models\Income\DeliveryOrder':
            return $this->base->delivery_order->number;

            case 'App\Models\Income\PreDelivery':
            return $this->base->pre_delivery->number;

            return null;
        }
    }
}
