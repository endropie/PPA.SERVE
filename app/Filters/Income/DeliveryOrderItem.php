<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;

class DeliveryOrderItem extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function date($value) {
        return $this->builder
            ->whereHas('delivery_order', function($q) use($value) {
                return $q->where('date', $value);
            });
    }

    public function customer_id($value) {
        return $this->builder
            ->whereHas('delivery_order', function($q) use($value) {
                    return $q->where('customer_id', $value);
            });
    }

}
