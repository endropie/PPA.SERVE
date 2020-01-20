<?php

namespace App\Models\Warehouse;

use App\Filters\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class OpnameVoucher extends Model
{
    use Filterable, SoftDeletes;

    protected $fillable = [
        'number', 'item_id', 'stockist', 'quantity', 'unit_id', 'unit_rate'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = ['opname_stock'];

    protected $casts = [
        'quantity' => 'double',
        'unit_amount' => 'double',
    ];

    public function opname_stock()
    {
        return $this->belongsTo('App\Models\Warehouse\OpnameStock');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getOpnameNumberAttribute()
    {
        if (!$opname_stock = $this->opname_stock) return null;
        if (!$opname = $opname_stock->opname) return null;

        return  $opname->full_number ?? $opname->number;
    }

    public function getUnitAmountAttribute()
    {
        if($this->unit_rate <= 0) return;

        return (double) $this->quantity * $this->unit_rate;
    }
}
