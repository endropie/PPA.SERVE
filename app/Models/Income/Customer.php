<?php
namespace App\Models\Income;

use App\Models\Model;
use App\Filters\Filterable;
use Endropie\AccurateClient\Traits\AccurateTrait;

class Customer extends Model
{
    use Filterable, AccurateTrait;

    protected $accurate_model = 'customer';

    protected $accurate_push_attributes = [
        'name' => 'name',
        'customerNo' => 'code',
        'notes' => 'description',
    ];

    protected $accurate_push_casts = [
        'notes' => 'String',
    ];

    protected $fillable = [
        'code', 'name', 'phone', 'fax', 'email', 'address', 'subdistrict', 'district', 'province_id', 'zipcode',
        'bank_account', 'npwp', 'pkp', 'with_ppn', 'with_pph', 'ppn', 'sen_service', 'exclude_service', 'bounded_service', 'description', 'enable',
        'invoice_mode', 'invoice_request_required', 'invoice_category_price',
        'delivery_mode', 'delivery_manual_allowed',
        'order_mode', 'order_manual_allowed', 'order_monthly_actived', 'order_lots'
    ];

    protected $appends = [ 'address_raw', 'is_invoice_request' ];

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

    public function customer_items() {
        return $this->hasMany('App\Models\Common\Item');
    }

    public function category_item_prices() {
        return $this->hasMany('App\Models\Common\CategoryItemPrice');
    }

    public function province()
    {
        return $this->belongsTo('App\Models\Reference\Province');
    }

    public function getAddressRawAttribute() {
        $raw  = ($this->address ?? '');
        $raw .= ($this->subdistrict ? "\n". $this->subdistrict .' ' : '');
        $raw .= ($this->district ?  $this->district .', ' : '');
        $raw .= ($this->province_id ? "\n". $this->province()->value('name') .' ' : '');
        $raw .= ($this->zipcode ? ' '. $this->zipcode : '');

        return $raw;
    }

    public function getIsInvoiceRequestAttribute() {
        if ($this->order_mode != 'NONE') return false;

        return $this->invoice_request_required;
    }
}
