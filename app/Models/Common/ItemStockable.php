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

    public function getBaseDataAttribute() {
        switch ($this->base_type) {
            case 'App\Models\Warehouse\IncomingGoodItem':
            return $this->base->incoming_good;

            case 'App\Models\Factory\WorkOrderItem':
            return $this->base->work_order;

            case 'App\Models\Factory\WorkProductionItem':
            return $this->base->work_production;

            case 'App\Models\Factory\PackingItem':
            return $this->base->packing;

            case 'App\Models\Income\RequestOrderItem':
            return $this->base->request_order;

            case 'App\Models\Income\DeliveryOrder':
            return $this->base->delivery_order;

            return null;
        }
    }

    public function getBaseCreatedAtAttribute() {
        return $this->base->created_at->toDateTimeString();
    }
}
