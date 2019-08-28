<?php
namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Filters\Filterable;

class IncomingGood extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'registration', 'date', 'time', 'transaction', 'order_mode',
        'customer_id', 'reference_number', 'reference_date', 'request_order_id',
        'vehicle_id', 'transport_rate', 'description',
    ];

    protected $relationships = [
        'pre_delivery',
        'request_order',
        'request_order.delivery_orders' => 'delivery_orders'
    ];

    protected $hidden = [];

    public function incoming_good_items()
    {
        return $this->hasMany('App\Models\Warehouse\IncomingGoodItem')->withTrashed();
    }

    public function request_order() {
        return $this->belongsTo('App\Models\Income\RequestOrder');
    }

    public function pre_delivery() {
        return $this->belongsTo('App\Models\Income\PreDelivery');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }
}