<?php

namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;
use Endropie\AccurateClient\Traits\AccurateTrait;

class Customer extends Model
{
    use Filterable, AccurateTrait;

    protected $accurate_model = 'customer';

    protected $fillable = [
        'code', 'name', 'phone', 'fax', 'email', 'address', 'subdistrict', 'district', 'province_id', 'zipcode',
        'bank_account', 'npwp', 'pkp', 'with_ppn', 'with_pph', 'ppn', 'sen_service', 'exclude_service', 'bounded_service', 'description', 'enable',
        'invoice_mode', 'invoice_request_required', 'invoice_category_price',
        'delivery_mode', 'delivery_manual_allowed', 'delivery_over_allowed',
        'order_mode', 'partialidate_allowed', 'order_manual_allowed', 'order_monthly_actived', 'order_lots'
    ];

    protected $appends = ['address_raw', 'is_invoice_request'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'ppn' => 'double',
        'sen_service' => 'double',
    ];

    protected $relationships = ['items'];

    public function customer_contacts()
    {
        return $this->hasMany('App\Models\Income\CustomerContact');
    }

    public function customer_trips()
    {
        return $this->hasMany('App\Models\Income\CustomerTrip');
    }

    public function trips()
    {
        return $this->hasMany('App\Models\Income\Trip');
    }

    public function delivery_tasks()
    {
        return $this->hasMany('App\Models\Income\DeliveryTask');
    }

    public function customer_items()
    {
        return $this->hasMany('App\Models\Common\Item');
    }

    public function category_item_prices()
    {
        return $this->hasMany('App\Models\Common\CategoryItemPrice');
    }

    public function province()
    {
        return $this->belongsTo('App\Models\Reference\Province');
    }

    public function getAddressRawAttribute()
    {
        $raw  = ($this->address ?? '');
        $raw .= ($this->subdistrict ? "\n" . $this->subdistrict . ' ' : '');
        $raw .= ($this->district ?  $this->district . ', ' : '');
        $raw .= ($this->province_id ? "\n" . $this->province()->value('name') . ' ' : '');
        $raw .= ($this->zipcode ? ' ' . $this->zipcode : '');

        return $raw;
    }

    public function getIsInvoiceRequestAttribute()
    {
        if ($this->order_mode != 'NONE') return false;

        return $this->invoice_request_required;
    }

    public function getDateTripsAttribute()
    {
        $date = request('trip_date', now()->format('Y-m-d'));
        return $this->trips()->where('date', $date)->get();
    }

    protected  static function booted()
    {
        static::registerModelEvent('accurate.pushing', function ($model, $record) {
            return array_merge($record, [
                'name' => (string) $model->name,
                'customerNo' => (string) $model->code,
                'notes' => (string) $model->description,
                'pkpNo' => (string) $model->pkp,
                'npwpNo' => (string) $model->npwp,
                'billStreet' => (string) $model->address_raw,
                // 'billCity' => (string) $model->district ,
                // 'billProvince' => (string) $model->province ? $model->province->name : null,
                'billZipCode' => (string) $model->zipcode,
                'workPhone' => (string) $model->phone,
                'fax' => (string) $model->fax,
                'email' => (string) $model->email,
            ]);
        });
    }
}
