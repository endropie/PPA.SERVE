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

    public function date($value) {
        return $this->builder->whereHas('work_order',  function($q) use($value) {
            return $q->where('date', $value);
        });
    }

    public function line_id($value) {
        return $this->builder->whereHas('work_order',  function($q) use($value) {
            return $q->where('line_id', $value);
        });
    }

    public function stockist_from($value) {
        return $this->builder->whereHas('work_order',  function($q) use($value) {
            return $q->where('stockist_from', $value);
        });
    }

    public function shift_id($value) {
        return $this->builder->whereHas('work_order',  function($q) use($value) {
            return $q->where('shift_id', $value);
        });
    }

    public function has_amount_production($value) {
        return $this->builder
            ->whereRaw('(quantity * unit_rate) > amount_process')
            ->whereHas('work_order', function($q) {
                if ($ondate = request('ondate', null)) {
                    $begin = date_format(date_modify(date_create($ondate), "-1 days"), "Y-m-d");
                    $until = date_format(date_modify(date_create($ondate), "+1 days"), "Y-m-d");
                    $q->whereBetween('date', [$begin, $until]);
                }
                if ($online = request('online', null)) {
                    $q->where('line_id', $online);
                }
                return $q->whereNull('stockist_direct')->where('status', '<>', 'CLOSED')->stateHasNot('PRODUCTED');
            });
    }

    public function has_amount_packing($value) {
        return $this->builder
            ->whereRaw('amount_process > amount_packing')
            ->whereHas('work_order', function($q) {
                return $q->whereNull('main_id')->where('status', '<>', 'CLOSED')->stateHasNot('PACKED');
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
