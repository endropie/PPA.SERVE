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
        'item_id', 'stockist', 'init_amount'
    ];

    protected $appends = [];

    protected $casts = [
        'final_amount' => 'double',
        'init_amount' => 'double',
        'move_amount' => 'double',
    ];

    protected $relationships = [];

    protected $hidden = [];

    public function opname_vouchers()
    {
        return $this->hasMany('App\Models\Warehouse\OpnameVoucher');
    }

    public function opname()
    {
        return $this->belongsTo('App\Models\Warehouse\Opname')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function stockable()
    {
        return $this->morphMany('App\Models\Common\ItemStockable', 'base');
    }


    public function getFinalAmountAttribute() {
        return (double) ($this->init_amount + $this->move_amount);
    }

    public function getOpnameNumberAttribute() {
        $opname = $this->opname;
        return (string) ($opname->full_number ?? $opname->number);
    }

}
