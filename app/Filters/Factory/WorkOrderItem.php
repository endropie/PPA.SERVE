<?php
namespace App\Filters\Factory;

use App\Filters\Filter;
use Illuminate\Http\Request;

class WorkOrderItem extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function customer_id($value) {
        return $this->builder->whereHas('item',  function($q) use($value) {
            return $q->where('customer_id', $value);
        });
    }

    public function has_amount_packing($value) {
        return $this->builder
            ->whereRaw('amount_process > amount_packing')
            ->whereHas('work_order', function($q) {
                return $q->where('status', '<>', 'CLOSED')->stateHasNot('PACKED');
            });
    }

    public function or_detail_ids($value = '') {
        if (!strlen($value)) return $this->builder;

        $value = explode(',',$value);
        return $this->builder->orWhere(function($q) use($value){
            return $q->whereIn('id',  $value);
        });
    }
}
