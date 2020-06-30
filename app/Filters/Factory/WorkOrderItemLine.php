<?php
namespace App\Filters\Factory;

use App\Filters\Filter;
use Illuminate\Http\Request;

class WorkOrderItemLine extends Filter
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
            ->whereHas('work_order_item', function($q) use($value) {
                return $q->whereHas('work_order', function($q) use($value) {
                    return $q->where('date', $value);
                });
            });
    }

    public function shift_id($value) {
        if(request()->has('has_amount_line')) return $this->builder;
        return $this->builder
            ->whereHas('work_order_item', function($q) use($value) {
                return $q->whereHas('work_order', function($q) use($value) {
                    return $q->where('shift_id', $value);
                });
            });
    }

    public function stockist_from($value) {
        return $this->builder
            ->whereHas('work_order_item', function($q) use($value) {
                return $q->whereHas('work_order', function($q) use($value) {
                    return $q->where('stockist_from', $value);
                });
            });
    }

    public function customer_id($value) {
        return $this->builder
            ->whereHas('work_order_item', function($q) use($value) {
                return $q->whereHas('item', function($q) use($value) {
                    return $q->where('customer_id', $value);
                });
            });
    }

    public function item_id($value) {
        return $this->builder
            ->whereHas('work_order_item', function($q) use($value) {
                return $q->where('item_id', $value);
            });
    }

    public function has_amount_line($value) {
        return $this->builder
            // ->whereRaw('amount_process > amount_packing')
            ->whereHas('work_order_item', function($q) {
                return $q->whereHas('work_order', function($q) {
                    if ($date = request('date', null)) $q->where('date', $date);
                    if ($shift_id = request('shift_id', null)) $q->where('shift_id', $shift_id);
                    if ($ondate = request('ondate', null)) $q->where('date', '<=', $ondate);
                    if ($onshift = request('onshift', null)) $q->where('shift_id', '<=', $onshift);
                    // abort(501, 'NONONO!');
                    return $q->whereNull('stockist_direct')->where('status', '<>', 'CLOSED')->stateHasNot('PRODUCTED');
                });
            });
    }

    public function or_detail_line_ids($value = '') {
        if (!strlen($value)) return $this->builder;
        $value = explode(',',$value);
        return $this->builder->orWhere(function($q) use($value){
            return $q->whereIn('id',  $value);
        });
    }
}
