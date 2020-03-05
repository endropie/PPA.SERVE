<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PreDelivery extends Filter
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

    public function customer_id($value) {
        return $this->builder->where('customer_id', $value);
    }

    public function available_outgoing_verification($value) {
        if ($value != 'true') return $this->builder;
        return $this->builder
        ->where('status', 'OPEN')
        ->whereHas('pre_delivery_items', function($query) {
            return $query->whereRaw('amount_verification < (quantity * unit_rate)');;
        });
    }
}
