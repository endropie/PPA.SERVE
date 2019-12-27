<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class OpnameStockItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'quantity', 'unit_id', 'unit_rate'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'unit_amount' => 'double'
    ];

    public function opname_stock()
    {
        return $this->belongsTo('App\Models\Warehouse\OpnameStock');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

    public function getUnitAmountAttribute()
    {
        return (double) ($this->quantity) * (double) ($this->unit_rate);
    }
}
