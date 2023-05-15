<?php

namespace App\Filters\Common;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Item extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function request_order_id($value = '')
    {
        if (!strlen($value)) return $this->builder;
        return $this->builder->whereHas('request_order_items', function ($q) use ($value) {
            return $q->where('request_order_id', $value);
        });
    }

    public function invoice_id($value = '')
    {
        if (!strlen($value)) return $this->builder;
        return $this->builder->whereHas('delivery_order_items', function ($q) use ($value) {
            return $q->whereHas('delivery_order', function ($q) use ($value) {
                return $q->where('acc_invoice_id', $value);
            });
        });
    }

    public function delivery_date($value = '')
    {
        if (!strlen($value)) return $this->builder;
        return $this->builder->whereHas('delivery_task_items', function ($q) use ($value) {
            return $q->whereHas('delivery_task', function ($q) use ($value) {
                return $q->where('date', $value);
            });
        });
    }

    public function delivery_verify_date($value = '')
    {
        // if (!strlen($value)) return $this->builder;
        return $this->builder->whereHas('delivery_verify_items', function ($q) use ($value) {
            return $q->where('date', $value);
        });
    }

    public function or_ids($value = '')
    {
        if (!strlen($value)) return $this->builder;

        $value = explode(',', $value);
        return $this->builder->orWhereIn('id', $value);
    }

    public function sample_in($value = '')
    {
        if (!strlen($value) || $value == 'REGULER') return $this->builder;

        return $this->builder->sampled()
            ->when($value === 'SAMPLE:DEPICT', function ($q) {
                return $q->whereNull('sample_depicted_at')->where('project', 'NEW');
            })
            ->when($value === 'SAMPLE:ENGINERY', function ($q) {
                return $q->whereNull('sample_enginered_at')->where(function ($q) {
                    return $q->orWhere('project', 'MIGRATE')
                        ->orWhere(function ($q) {
                            return $q->where('project', 'NEW')->whereNotNull('sample_depicted_at');
                        });
                });
            })
            ->when($value === 'SAMPLE:PRICE', function ($q) {
                return $q->whereNull('sample_priced_at')->whereNotNull('sample_enginered_at');
            })
            ->when($value === 'SAMPLE:VALIDATE', function ($q) {
                return $q->whereNull('sample_validated_at')->whereNotNull('sample_enginered_at')->whereNotNull('sample_priced_at');
            });
    }

    public function has_stocks($value = '')
    {
        if (!strlen($value)) return $this->builder;

        $value = explode(',', strtoupper($value));
        if (in_array('ALL', $value)) $value = ['FM', 'WO', 'WIP', 'FG', 'NC', 'NCR'];

        return $this->builder->whereHas('item_stocks', function ($q) use ($value) {
            if (count($value) > 0) $q->whereIn('stockist', $value);
            return $q->where('total', '>', 0);
        });
    }

    public function main_line($value = 0)
    {
        return $this->builder->whereHas('item_prelines', function ($q) use ($value) {
            return $q->where('line_id', strtoupper($value))->where('ismain', 1);
        });
    }

    public function mode_line($value = 'ALL')
    {
        $value = strtoupper($value);
        if (!in_array($value, ['SINGLE', 'MULTI'])) return $this->builder;
        return $this->builder->whereHas('item_prelines', function ($q) use ($value) {
            $a = $value == 'SINGLE' ? "=" : "<";
            return $q->whereRaw(DB::raw("1 $a (SELECT COUNT(*) FROM `item_prelines` WHERE `items`.`id` = `item_prelines`.`item_id`)"));
        });
    }

    public function sort_ALL($order = '')
    {
        $stockists = '"FM", "WO", "WIP", "FG", "NC", "NCR"';
        return $this->builder->select(
            'items.*',
            DB::raw("(SELECT SUM(total) FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist IN ($stockists)) as fieldsort")
        )
            ->orderBy('fieldsort', $order);
    }

    public function sort_FM($order = '')
    {
        $stockist = 'FM';
        return $this->builder->select(
            'items.*',
            DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort")
        )
            ->orderBy('fieldsort', $order);
    }

    public function sort_WO($order = '')
    {
        $stockist = 'WO';
        return $this->builder->select(
            'items.*',
            DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort")
        )
            ->orderBy('fieldsort', $order);
    }

    public function sort_WIP($order = '')
    {
        $stockist = 'WIP';
        return $this->builder->select(
            'items.*',
            DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort")
        )
            ->orderBy('fieldsort', $order);
    }

    public function sort_FG($order = '')
    {
        $stockist = 'FG';
        return $this->builder->select(
            'items.*',
            DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort")
        )
            ->orderBy('fieldsort', $order);
    }

    public function sort_NC($order = '')
    {
        $stockist = 'NC';
        return $this->builder->select(
            'items.*',
            DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort")
        )
            ->orderBy('fieldsort', $order);
    }

    public function sort_NCR($order = '')
    {
        $stockist = 'NCR';
        return $this->builder->select(
            'items.*',
            DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort")
        )
            ->orderBy('fieldsort', $order);
    }

    public function search($value = '')
    {
        if (!strlen($value)) return $this->builder;

        if ($fields = request('search-keys')) {
            $fields = explode(',', $fields);
        } else {

            $tableName = $this->builder->getQuery()->from;
            $fields = Schema::getColumnListing($tableName);
            $except = [$this->builder->getModel()->getKeyName()];
            $fields = array_diff_key($fields, $except);
        }


        $separator = substr_count($value, '|') > 0 ? '|' : ' ';
        $keywords = explode($separator, $value);
        return $this->builder->where(function ($query) use ($fields, $keywords) {
            foreach ($keywords as $keyword) {
                if (strlen($keyword)) {
                    $query->where(function ($query) use ($fields, $keyword) {
                        foreach ($fields as $field) {
                            $query->orWhere($field, 'like', '%' . $keyword . '%');
                        }

                        $query->orWhereHas('customer', function ($query) use ($keyword) {
                            $query->where('code', 'like', '%' . $keyword . '%');
                        });
                    });
                }
            }
        });
    }
}
