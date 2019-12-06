<?php
namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestOrder extends Model
{
    use Filterable, SoftDeletes;

    protected $fillable = [
        'number', 'date', 'customer_id', 'reference_number', 'description', 'transaction', 'order_mode', 'is_estimate', 'estimate_number'
    ];

    protected $relationships = [
        'delivery_orders',
        // 'request_order_items.delivery_order_items',
    ];

    public function request_order_items()
    {
        return $this->hasMany('App\Models\Income\RequestOrderItem')->withTrashed();
    }

    public function delivery_orders() {
        return $this->hasMany('App\Models\Income\DeliveryOrder');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }
}
