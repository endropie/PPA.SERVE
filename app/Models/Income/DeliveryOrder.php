<?php

namespace App\Models\Income;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Filters\Filterable;

class DeliveryOrder extends Model
{
    use Filterable, SoftDeletes;

    protected $fillable = [
        'number', 'revise_number', 'customer_id', 'customer_name', 'customer_phone', 'customer_address', 'description',
        'transaction', 'date', 'due_date', 'operator_id', 'vehicle_id', 'transport_rate',
    ];

    protected $hidden = [];

    protected $relationships = [
        'request_order_closed'
    ];

    public function delivery_order_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrderItem')->withTrashed();
    }

    public function outgoing_goods()
    {
        return $this->hasMany('App\Models\Warehouse\OurgoingGood');
    }

    public function request_order()
    {
        return $this->belongsTo('App\Models\Income\RequestOrder');
    }

    public function request_order_closed()
    {
        return $this->request_order()->where('status', 'CLOSED');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Reference\Vehicle');
    }

    public function operator()
    {
        return $this->belongsTo('App\Models\Common\Employee');
    }

    public function getHasRevisionAttribute()
    {
        return $this->hasMany(get_class($this),'id')->where('number', $this->number)->where('id', '!=', $this->id);
    }
}
