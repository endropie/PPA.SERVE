<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreDelivery extends Model
{
    use Filterable, SoftDeletes;

    protected $fillable = [
        'number', 'customer_id', 'description',
        'transaction', 'order_mode', 'rit', 'date', // 'plan_begin_date', 'plan_until_date'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [
        // 'incoming_good'
    ];

    public function pre_delivery_items()
    {
        return $this->hasMany('App\Models\Income\preDeliveryItem')->withTrashed();
    }

    public function incoming_good() {
        return $this->hasOne('App\Models\Warehouse\IncomingGood');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }
}
