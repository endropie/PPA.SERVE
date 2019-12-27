<?php
namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;

class OutgoingGood extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'customer_id', 'customer_name', 'customer_phone', 'customer_address', 'customer_note', 'description',
        'transaction', 'date', 'due_date', 'operator_id', 'vehicle_id', 'transport_rate'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [
        'revise_delivery_orders' => 'delivery_orders.revise',
        'delivery_orders',
        'request_order_closed',             //=> GENERATE DO => request order CLOSED state.
        // 'request_order_items_closed',        //=> ORDER "ACCUMULATE" => request order CLOSED state.
    ];

    public function outgoing_good_items()
    {
        return $this->hasMany('App\Models\Warehouse\OutgoingGoodItem')->withTrashed();
    }

    public function delivery_orders()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrder');
    }

    public function revise_delivery_orders()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrder')->whereNotNull('revise_id');
    }

    public function request_order()
    {
        return $this->belongsTo('App\Models\Income\RequestOrder');
    }

    public function request_order_closed()
    {
        return $this->request_order()->where('status', 'CLOSED');
    }

    public function request_order_items()
    {
        return $this->hasMany('App\Models\Income\RequestOrderItem');
    }

    // public function request_order_items_closed()
    // {
    //     return $this->request_order_items()->whereHas('request_order', function($query) {
    //         $query->where('status', 'CLOSED');
    //     });
    // }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function operator()
    {
        return $this->belongsTo('App\Models\Common\Employee');
    }
}
