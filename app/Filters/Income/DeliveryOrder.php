<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;

class DeliveryOrder extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function begin_date($value) {
        return $this->builder->where('date', '>=',  $value);
    }

    public function until_date($value) {
        return $this->builder->where('date', '<=',  $value);
    }

    public function customer_id($value)
    {
        return $this->builder->where('customer_id', $value);
    }

    public function status ($value)
    {
        switch (strtoupper($value)) {
            default:
                return $this->builder->where('status', $value);
                break;
        }
    }

    public function request_reference_number ($value = null)
    {
        if (!$value) return $this->builder;
        return $this->builder->whereHas('request_order', function($q) use ($value) {
            return $q->where(function ($q) use ($value) {
                foreach (explode('+', $value) as $str) {
                    $q->orWhere('reference_number', $str);
                }

                return $q;
            });
        });
    }

    public function has_checkout($value = '')
    {
        if (!strlen($value)) return $this->builder;
        return $this->builder->when((boolean) $value,
            function ($q) {
                return $q->whereNotNull('delivery_checkout_id');
            },
            function ($q) {
                return $q->whereNull('delivery_checkout_id');
            });
    }

    public function has_load($value = '')
    {
        if (!strlen($value)) return $this->builder;
        return $this->builder->when((boolean) $value,
            function ($q) {
                return $q->whereNotNull('delivery_load_id');
            },
            function ($q) {
                return $q->whereNull('delivery_load_id');
            });
    }

    public function invoicing($order = 'true')
    {
        return $this->builder->where('transaction', 'REGULER')->where('is_internal', 0)
            ->where(function($q) {
                return $q->whereNull('acc_invoice_id')
                ->when(request('or_acc_invoice_id'), function($q) {
                    return $q->orWhere('acc_invoice_id', request('or_acc_invoice_id'));
                });
            });
    }

    public function handovering($order = 'true')
    {
        return $this->builder
            // ->where('transaction', 'REGULER')->where('is_internal', 0)
            ->where(function($q) {
                return $q->whereNull('delivery_handover_id')
                ->when(request('or_delivery_handover_id'), function($q) {
                    return $q->orWhere('delivery_handover_id', request('or_delivery_handover_id'));
                });
            });
    }

}
