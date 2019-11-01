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
            case 'OPEN':
                return $this->builder->where('status', 'OPEN')
                    ->whereDoesntHave('work_order_item_lines.work_production_items')
                    ->whereDoesntHave('work_order_items.packing_items');
                break;
            case 'PRODUCTION':
                return $this->builder->where('status', 'OPEN')
                    ->whereHas('work_order_item_lines.work_production_items')
                    ->whereHas('work_order_items', function($q) {
                        $q->havingRaw('ROUND(quantity*unit_rate, 0) <> ROUND(amount_process, 0)');
                    });
                break;
            case 'PACKING':
                return $this->builder->where('status', 'OPEN')
                    ->where(function($q) {
                        $q->orWhereHas('work_order_item_lines.work_production_items')
                          ->orWhereHas('work_order_items.packing_items');
                    })
                    ->whereHas('work_order_items', function($q) {
                        $q->havingRaw('ROUND(quantity * unit_rate, 0) <> ROUND(amount_packing, 0)');
                    });
                break;
            case 'CLOSED:PRODUCTION':
                return $this->builder->where('status', 'OPEN')
                    ->whereDoesntHave('work_order_items', function($q) {
                        $q->havingRaw('ROUND(quantity * unit_rate, 0) <> ROUND(amount_process, 0)');
                    });
                break;
            case 'CLOSED:PACKING':
                return $this->builder->where('status', 'OPEN')
                    ->whereDoesntHave('work_order_items', function($q) {
                        $q->havingRaw('ROUND(quantity * unit_rate, 0) <> ROUND(amount_packing, 0)');
                    });
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

    public function or_detail_ids($value = '') {
        if (!strlen($value)) return $this->builder;

        $value = explode(',',$value);
        return $this->builder->orWhere(function($query) use($value){
            $query->whereHas('work_order_items', function ($q) use($value) {
                return $q->whereIn('id',  $value);
            });
        });
    }

    public function line_id($line) {
        return $this->builder->where('line_id', $line);
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
        $callback =  function ($q) {
          $or_details = explode(',', request('or_detail_ids', ''));
          $q->whereRaw('amount_process > amount_packing')
            ->orWhereIn('item_id', $or_details)
            ->whereHas('item', function ($q) {
                if (request('customer_id')) $q->where('customer_id', request('customer_id'));
            });

        };
        return $this->builder
            ->with(['work_order_items'])
            ->whereHas('work_order_items', $callback);
    }

    public function has_amount_line($line) {
        $keys = request('or_work_order_item_line_ids', '-1');
        $callback = function ($q) use ($line, $keys) {
            return $q->where('line_id', $line)
                     ->select('work_order_item_lines.*',
                     \DB::raw("(SELECT quantity*unit_rate as total
                                FROM work_order_items WHERE work_order_items.id = work_order_item_lines.work_order_item_id) as amount"))
                     ->havingRaw("amount > amount_line OR work_order_item_lines.id IN ($keys)");

        };

        if((int) $line) {
            return $this->builder
            ->where('status', '<>', 'CLOSED')
            ->with(['work_order_item_lines' => $callback])
            ->whereHas('work_order_item_lines', $callback);
        }
        // else return $this->builder;
    }
}
