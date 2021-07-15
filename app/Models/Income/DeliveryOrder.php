<?php

namespace App\Models\Income;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;

class DeliveryOrder extends Model
{
    use Filterable, SoftDeletes, WithUserBy, HasCommentable;

    protected $fillable = [
        'number', 'indexed_number', 'revise_number', 'customer_id', 'description', 'is_internal',
        'transaction', 'date', 'vehicle_id', 'rit',
        'customer_name', 'customer_phone', 'customer_address', 'customer_note'
    ];

    protected $appends = ['fullnumber', 'fullnumber_index', 'fullnumber_revise', 'request_reference_number'];

    protected $hidden = [];

    protected $relationships = [
        'request_order_closed',
        'acc_invoice'
    ];

    protected $casts = [
        'is_internal' => 'bool'
    ];

    public function delivery_order_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrderItem')->withTrashed();
    }

    public function delivery_checkout()
    {
        return $this->belongsTo('App\Models\Income\DeliveryCheckout');
    }

    public function acc_invoice()
    {
        return $this->belongsTo('App\Models\Income\AccInvoice');
    }

    public function request_order()
    {
        return $this->belongsTo('App\Models\Income\RequestOrder');
    }

    public function delivery_load()
    {
        return $this->belongsTo('App\Models\Income\DeliveryLoad');
    }

    public function request_order_closed()
    {
        return $this->request_order()->where('status', 'CLOSED');
    }

    public function revise()
    {
        return $this->belongsTo('App\Models\Income\DeliveryOrder')->withTrashed();
    }

    public function revisions()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrder', 'revise_id')->withTrashed();
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

    public function reason()
    {
        return $this->belongsTo('App\Models\Reference\Reason');
    }

    public function confirmed_user()
    {
        return $this->belongsTo('App\Models\Auth\User', 'confirmed_by');
    }

    public function getSummaryItemsAttribute()
    {
        return (float) $this->hasMany('App\Models\Income\DeliveryOrderItem')->get()->sum('quantity');
    }

    public function getHasRevisionAttribute()
    {
        return $this->revisions;
    }

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number . " R." . (int) $this->revise_number;

        return $this->number;
    }

    public function getRequestReferenceNumberAttribute()
    {
        if ($request_order = app('App\Models\Income\RequestOrder')->find($this->request_order_id)) {
            return (string) $request_order->reference_number;
        }
        return null;
    }

    public function getFullnumberIndexAttribute()
    {
        if ($this->indexed_number && $this->revise_number) return $this->indexed_number . " R." . (int) $this->revise_number;

        return $this->indexed_number;
    }

    public function getFullnumberReviseAttribute()
    {
        $revise = app('App\Models\Income\DeliveryOrder')->withTrashed()->find($this->revise_id);
        if (!$revise) return null;

        return $revise->revise_id && $revise->revise_number
            ? $revise->number . " R." . (int) $revise->revise_number
            : $revise->number;
    }

    public function getConfirmedUserAttribute()
    {
        return $this->confirmed_user()->first();
    }
}
