<?php

namespace App\Models\Factory;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'quantity', 'unit_id', 'unit_rate'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];

    public function production()
    {
        return $this->belongsTo('App\Models\Factory\Production');
    }

    public function work_order_item()
    {
        return $this->belongsTo('App\Models\Factory\WorkOrderItem');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Reference\Unit');
    }

}
