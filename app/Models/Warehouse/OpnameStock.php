<?php
namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Filters\Filterable;

class OpnameStock extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'date', 'item_id', 'stockist', 'init_amount', 'description'
    ];

    protected $appends = [];

    protected $casts = [
        'init_amount' => 'double',
        'final_amount' => 'double',
        'total_amount' => 'double',
    ];

    protected $relationships = [];

    protected $hidden = [];

    public function opname_stock_items()
    {
        return $this->hasMany('App\Models\Warehouse\OpnameStockItem')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function stockable()
    {
        return $this->morphMany('App\Models\Common\ItemStockable', 'base');
    }

    public function getTotalAmountAttribute() {
        return (double) ($this->opname_stock_items->sum('unit_amount') - $this->init_amount);
    }

    public function getFinalAmountAttribute() {
        return (double) $this->init_amount + $this->total_amount;
    }
}
