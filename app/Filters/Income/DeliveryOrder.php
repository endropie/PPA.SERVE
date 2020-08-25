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

    public function customer_id($value) 
    {
        return $this->builder->where('customer_id', $value);
    }

    public function status ($value) 
    {
        switch (strtoupper($value)) {
            case 'RECONCILIATION':
                return $this->builder->where('is_internal', 1)
                    ->whereHas('delivery_order_items', function($q) {
                        $q->whereRaw('amount_reconcile < (quantity * unit_rate)');
                    });
                break;
            case 'RECONCILED':
                return $this->builder->where('is_internal', 1)
                    ->whereDoesntHave('delivery_order_items', function($q) {
                        $q->whereRaw('ROUND(amount_reconcile) <> ROUND(quantity * unit_rate)');
                    });
            default:
                return $this->builder->where('status', $value);
                break;
        }
    }

    public function invoicing($order = 'true') 
    {
        return $this->builder->whereNull('acc_invoice_id');
    }

}
