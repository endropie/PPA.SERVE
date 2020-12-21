<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;

class RequestOrderItem extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function begin_date($value) {
        if (!strlen($value)) return $this->builder;
        return $this->builder->whereHas('request_order',function($q) use($value) {
            return $q->where('date', '>=',  $value);
        });
    }

    public function until_date($value) {
        if (!strlen($value)) return $this->builder;
        return $this->builder->whereHas('request_order',function($q) use($value) {
            return $q->where('date', '<=',  $value);
        });
    }

    public function delivery_date ($value = '') {
        if (!strlen($value)) return $this->builder;
        return $this->builder->whereHas('item', function($q) use($value) {
            return $q->whereHas('delivery_task_items', function($q) use($value) {
                return $q->whereHas('delivery_task', function($q) use ($value){
                    return $q->where('date', $value);
                });
            });
        });
    }
}
