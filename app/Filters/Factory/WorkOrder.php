<?php
namespace App\Filters\Factory;

use App\Filters\Filter;
use Illuminate\Http\Request;

class WorkOrder extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function or_ids($value = '') {
        if (!strlen($value)) return $this->builder;

        $value = explode(',',$value);
        return $this->builder->orWhereIn('id', $value);
    }

    public function or_detail_ids($value = '') {
        if (!strlen($value)) return $this->builder;

        $value = explode(',',$value);
        return $this->builder->orWhere(function($query) use($value){
            $query->whereHas('work_order_items', function ($q) use($value) {
                return $q->whereIn('id',  $value);
            });
        });
    }

    public function line_id($value) {
        return $this->builder->where('line_id', $value);
    }

    public function begin_date($value) {
        return $this->builder->where('date', '>=',  $value);
    }

    public function until_date($value) {
        return $this->builder->where('date', '<=',  $value);
    }

    public function item_id($value) {
        return $this->builder->whereHas('work_order_items', function ($q) use($value) {
            return $q->where('item_id',  $value);
        });
    }

    public function has_amount_packing($value) {

        if($value === 'true') {
            return $this->builder->whereHas('work_order_items', function ($q) {
                if (request('customer_id')) {
                    return $q->whereRaw('amount_process > amount_packing')
                             ->whereHas('item', function ($q) {
                                $q->where('customer_id', request('customer_id'));
                             });
                }
                return $q->whereRaw('amount_process > amount_packing');
            });
        }
        else return $this->builder;
    }
}
