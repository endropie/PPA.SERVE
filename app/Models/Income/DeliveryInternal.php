<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryInternal extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'date', 'reason_id', 'reason_description', 'description',
        'customer_id', 'customer_name', 'customer_phone', 'customer_address'
    ];

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function delivery_internal_items ()
    {
        return $this->hasMany('App\Models\Income\DeliveryInternalItem')->withTrashed();
    }
}
