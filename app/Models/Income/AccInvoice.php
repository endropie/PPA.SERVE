<?php
namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use Carbon\Carbon;
use Endropie\AccurateClient\Traits\AccurateTrait;

class AccInvoice extends Model
{
    use Filterable, WithUserBy, AccurateTrait;

    protected $accurate_model = 'sales-invoice';

    protected $accurate_push_attributes = [
        'transDate' => 'date',
        'customerNo' => 'customer.code'
    ];

    protected $accurate_push_casts = [
        'transDate' => 'Date',
    ];

    static function boot()
    {
        parent::boot();
        AccInvoice::accurateObserve(\App\Observers\Accurates\AccInvoiceObserver::class);
    }

    protected $fillable = [
        'number', 'date', 'customer_id', 'order_mode'
    ];

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function service_invoice()
    {
        return $this->hasOne(self::class, 'service_invoice_id');
    }

    public function material_invoice()
    {
        return $this->belongsTo(self::class, 'service_invoice_id');
    }

    public function delivery_orders()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrder');
    }

    public function request_orders()
    {
        return $this->hasMany('App\Models\Income\RequestOrder');
    }

    public function acc_invoice_items()
    {
        return $this->hasManyThrough('App\Models\Income\DeliveryOrderItem', 'App\Models\Income\DeliveryOrder');
    }

    public function getDeliveriesAttribute()
    {
        return $this->delivery_orders()->with([
            'delivery_order_items.item',
            'delivery_order_items.unit'
        ])->get();
    }

    public function getDeliveryItemsAttribute()
    {
        $class = $this->material_invoice ?? $this;
        return $class->hasManyThrough('App\Models\Income\DeliveryOrderItem', 'App\Models\Income\DeliveryOrder')->get();
    }

    public function getFullnumberAttribute()
    {
        return (string) $this->number;
    }
}
