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
        'item_id', 'unit_id', 'unit_rate', 'quantity', 'date',// 'pre_delivery_item_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_rate' => 'double',
        'quantity' => 'double'
    ];

    protected $relationships = [
        'validated',
    ];

    public function validated() {
        return $this->belongsTo('App\Models\Warehouse\OutgoingGoodVerification', 'id')->whereNotNull('validated_at');
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

    public function getUnitAmountAttribute() {
        // return false when rate is not valid
        if($this->unit_rate <= 0) return false;

        return (double) $this->quantity * $this->unit_rate;
    }
}
