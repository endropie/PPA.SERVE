<?php
namespace App\Filters\Common;

use App\Filters\Filter;
use Illuminate\Http\Request;

class Item extends Filter
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

    public function has_stocks($value = '') {
        if(!strlen($value)) return $this->builder;

        $value = explode(',',strtoupper($value));
        if(in_array('ALL', $value)) $value = ['FM','WO','WIP','FG','NG', 'RET'];

        return $this->builder->whereHas('item_stocks', function($q) use($value){
            if(count($value) > 0) $q->whereIn('stockist', $value);
            return $q->where('total', '>', 0);
        });
    }

    public function main_line($value = 0) {
        return $this->builder->whereHas('item_prelines', function($q) use($value){
            return $q->where('line_id', strtoupper($value));
        });
    }

    public function sort_ALL($order = '') {
        $stockists = '"FM", "WO", "WIP", "FG", "NG", "RET"';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT SUM(total) FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist IN ($stockists)) as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_FM($order = '') {
        $stockist = 'FM';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_WO($order = '') {
        $stockist = 'WO';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_WIP($order = '') {
        $stockist = 'WIP';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_FG($order = '') {
        $stockist = 'FG';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_NG($order = '') {
        $stockist = 'NG';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_RET($order = '') {
        $stockist = 'RET';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_PDOREG($order = '') {
        $stockist = 'PDO.REG';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_PDORET($order = '') {
        $stockist = 'PDO.RET';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function sort_VDO($order = '') {
        $stockist = 'VDO';
        return $this->builder->select('items.*',
            \DB::raw("(SELECT total FROM item_stocks WHERE items.id = item_stocks.item_id AND item_stocks.stockist = '$stockist') as fieldsort"))
        ->orderBy('fieldsort', $order);
    }

    public function search($value = '') {
        if(!strlen($value)) return $this->builder;

        if ($fields = request('search-keys')) {
            $fields = explode(',', $fields);
        }
        else {
            $tableName = $this->builder->getQuery()->from;
            $fields = \Schema::getColumnListing($tableName);
        }

        $separator = substr_count($value, '|') > 0 ? '|' : ' ';
        $keywords = explode($separator, $value);
        return $this->builder->where(function ($query) use ($fields, $keywords) {
            foreach ($keywords as $keyword) {
                if(strlen($keyword)) {
                  $query->where(function ($query) use ($fields, $keyword) {
                    foreach ($fields as $field) {
                        $query->orWhere($field, 'like', '%'.$keyword.'%');
                    }

                    $query->orWhereHas('customer', function($query) use ($keyword) {
                        $query->where('code', 'like', '%'.$keyword.'%');
                    });
                  });
                }
            }
        });
    }

}
