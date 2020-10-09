<?php
namespace App\Models\Income;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryInternalItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id', 'unit_id', 'quantity', 'name', 'subname', 'note'
    ];

    protected $appends = [];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'quantity' => 'double',
    ];

    public function delivery_internal()
    {
        return $this->belongsTo('App\Models\Income\DeliveryInternal');
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
