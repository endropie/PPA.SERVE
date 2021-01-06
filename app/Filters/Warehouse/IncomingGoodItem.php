<?php
namespace App\Filters\Warehouse;

use App\Filters\Filter;
use Illuminate\Http\Request;

class IncomingGoodItem extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function date($value) {
        if(request()->has('has_amount_line')) return $this->builder;
        return $this->builder
            ->whereHas('incoming_good', function($q) use($value) {
                return $q->where('date', $value);
            });
    }

    public function customer_id($value) {
        return $this->builder
            ->whereHas('incoming_good', function($q) use($value) {
                    return $q->where('customer_id', $value);
            });
    }

}
