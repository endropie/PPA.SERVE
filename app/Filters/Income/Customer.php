<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;

class Customer extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function trip_intday ($value = '') {
        return $this->builder->whereHas('customer_trips', function($q) use($value) {
            return $q->where('intday', $value);
        });
    }

    public function delivery_date ($value = '') {
        return $this->builder->whereHas('delivery_task_items', function($q) use($value) {
            return $q->whereHas('delivery_task', function($q) use ($value){
                return $q->where('date', $value);
            });
        });
    }

}
