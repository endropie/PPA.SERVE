<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    public function invoicing($order = 'true')
    {
        return $this->builder->where('transaction', 'REGULER')
            ->where(function($q) {
                return $q->whereNull('acc_invoice_id')
                ->when(request('or_acc_invoice_id'), function($q) {
                    return $q->orWhere('acc_invoice_id', request('or_acc_invoice_id'));
                });
            });
    }

}
