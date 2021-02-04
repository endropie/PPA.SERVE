<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;

class RequestOrder extends Filter
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

    public function invoicing($order = 'true') {
        return $this->builder
            ->where('transaction', 'REGULER')
            ->where(function($q) {
                return $q->whereNull('acc_invoice_id')
                ->when(request('or_acc_invoice_id'), function($q) {
                    return $q->orWhere('acc_invoice_id', request('or_acc_invoice_id'));
                });
            });
    }

    public function delivery_invoice_id($value) {
        return $this->builder
            ->whereHas('request_order_items', function($q) use($value) {
                return $q->whereHas('delivery_order_items', function($q) use($value) {
                    $invoice = \App\Models\Income\AccInvoice::find($value);
                    $ids = $invoice ? $invoice->acc_invoice_items->pluck('id') : [];
                    return $q->whereIn('id', $ids);
                });
            });
    }

    public function sort_counter_invoiced($order = '') {
        return $this->builder->select('request_orders.*',
            \DB::raw("(SELECT COUNT(*) FROM delivery_orders WHERE request_orders.id = delivery_orders.request_order_id AND delivery_orders.acc_invoice_id IS NOT NULL) as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_counter_confirmed($order = '') {
        return $this->builder->select('request_orders.*',
            \DB::raw("(SELECT COUNT(*) FROM delivery_orders WHERE request_orders.id = delivery_orders.request_order_id AND delivery_orders.acc_invoice_id IS NULL AND status = 'CONFIRMED') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_counter_delivered($order = '') {
        return $this->builder->select('request_orders.*',
            \DB::raw("(SELECT COUNT(*) FROM delivery_orders WHERE request_orders.id = delivery_orders.request_order_id AND delivery_orders.acc_invoice_id IS NULL AND status <> 'CONFIRMED') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

}
