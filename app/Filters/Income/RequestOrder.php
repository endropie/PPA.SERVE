<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    public function sort_counter_invoiced($order = '') {
        // abort(501, 'ini aku');
        return $this->builder->select('request_orders.*',
            \DB::raw("(SELECT COUNT(*) FROM delivery_orders WHERE request_orders.id = delivery_orders.request_order_id AND delivery_orders.acc_invoice_id IS NOT NULL) as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_counter_confirmed($order = '') {
        // abort(501, 'ini aku');
        return $this->builder->select('request_orders.*',
            \DB::raw("(SELECT COUNT(*) FROM delivery_orders WHERE request_orders.id = delivery_orders.request_order_id AND delivery_orders.acc_invoice_id IS NULL AND status = 'CONFIRMED') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_counter_delivered($order = '') {
        // abort(501, 'ini aku');
        return $this->builder->select('request_orders.*',
            \DB::raw("(SELECT COUNT(*) FROM delivery_orders WHERE request_orders.id = delivery_orders.request_order_id AND delivery_orders.acc_invoice_id IS NULL AND status <> 'CONFIRMED') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

}
