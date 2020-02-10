<?php

namespace App\Models\Income;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;

class DeliveryOrder extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'revise_number', 'customer_id', 'description', 'is_internal',
        'transaction', 'date', 'vehicle_id', 'transport_rate',
        'customer_name', 'customer_phone', 'customer_address', 'customer_note'
    ];

    protected $appends = ['fullnumber'];

    protected $hidden = [];

    protected $relationships = [
        'request_order_closed',
        'delivery_order_items.reconcile_items'
    ];

    protected $casts = [
        'is_internal' => 'bool'
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

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number ." REV.". (int) $this->revise_number;

        return $this->number;
    }
}
