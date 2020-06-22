<?php
namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use Endropie\AccurateClient\Traits\AccurateTrait;

class AccInvoice extends Model
{
    use Filterable, WithUserBy, AccurateTrait;

    protected $serviceMode = false;

    protected $accurate_model = 'sales-invoice';

    protected $accurate_push_attributes = [
        'number' => 'fullnumber',
        'transDate' => 'date',
        'customerNo' => 'request_order.customer.code'
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
        'number', 'date'
    ];

    public function request_order()
    {
        return $this->belongsTo('App\Models\Income\RequestOrder');
    }

    public function delivery_orders()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrder');
    }

    public function deliveries()
    {
        return $this->belongsToMany('App\Models\Income\DeliveryOrder');
    }

    public function acc_invoice_items()
    {
        return $this->hasManyThrough('App\Models\Income\DeliveryOrderItem', 'App\Models\Income\DeliveryOrder');
    }

    public function getFullnumberAttribute()
    {
        return (string) $this->number . ($this->serviceMode ? '.JASA' : '');
    }

    public function getServiceModeAttribute()
    {
        return (boolean) $this->serviceMode;
    }

    public function service()
    {
        $this->serviceMode = true;
        return $this;
    }
}
