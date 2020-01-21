<?php
namespace App\Filters\Warehouse;

use App\Filters\Filter;
use Illuminate\Http\Request;

class OpnameVoucher extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function opname_stock_id($value = null) {
        return $this->builder->where('opname_stock_id',  $value);
    }

    public function opname_id($value = null) {
        return $this->builder->whereHas('opname_stock',  function($query) use($value) {
            return $query->where('opname_id', $value);
        });
    }

    public function or_ids($value) {
        $ids = explode(',', $value);
        return $this->builder->orWhereIn('id',  $ids);
    }

    public function customer_id($value) {
        return $this->builder->whereHas('item', function($q) use($value) {
            return $q->where('customer_id', $value);
        });
    }

}
