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

    public function status ($value) {
        switch (strtoupper($value)) {
            case 'ON:PROCESS':
                return $this->builder->whereIn('status', ['OPEN', 'PRODUCTED', 'PACKED'])
                    ->whereHas('work_order_items', function($q) {
                        $q->where('amount_process', '>', 0);
                    });
                break;
            case 'ON:DIRECT':
                return $this->builder->where('status','OPEN')->whereNotNull('stockist_direct');
                break;
            case 'HAS:PRODUCTED':
                return $this->builder->stateHas('PRODUCTED');
                break;

            case 'HAS:PACKED':
                return $this->builder->stateHas('PACKED');
                break;
            default:
                return $this->builder->where('status', $value);
                break;
        }
    }

    public function or_ids($value = '') {
        if (!strlen($value)) return $this->builder;

        $value = explode(',',$value);
        return $this->builder->orWhereIn('id', $value);
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
}
