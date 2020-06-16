<?php
namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestOrder extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'date', 'customer_id', 'transaction', 'reference_number', 'description',
        'actived_date', 'order_mode'
    ];

    protected $appends = ['fullnumber'];

    protected $relationships = [
        'delivery_orders',
        // 'request_order_items.delivery_order_items',
    ];

    public function request_order_items()
    {
        return $this->hasMany('App\Models\Income\RequestOrderItem')->withTrashed();
    }

    public function delivery_order_items()
    {
        return $this->hasManyThrough('App\Models\Income\DeliveryOrderItem', 'App\Models\Income\RequestOrderItem', 'request_order_id',  'request_order_item_id');
    }

    public function delivery_orders() {
        return $this->hasMany('App\Models\Income\DeliveryOrder');
    }

    public function acc_invoices() {
        return $this->hasMany('App\Models\Income\AccInvoice');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function getDeliveryCounterAttribute() {
        $all = $this->delivery_orders;
        $confirmed = $all->where('status', 'CONFIRMED');
        $invoiced = $confirmed->whereNotNull('acc_invoice_id');

        return [
            'all' => $all->count(),
            'delivered' => $all->count() - $confirmed->count(),
            'confirmed' => $confirmed->count() - $invoiced->count(),
            'invoiced' => $invoiced->count()
        ];
    }

    public function getTotalUnitAmountAttribute() {
        return (double) $this->request_order_items->sum('unit_amount');
    }

    public function getTotalUnitDeliveryAttribute() {
        return (double) $this->delivery_order_items->sum('unit_amount');
    }

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number ." R.". (int) $this->revise_number;

        return $this->number;
    }
}
