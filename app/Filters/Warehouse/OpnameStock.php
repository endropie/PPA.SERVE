<?php
namespace App\Filters\Warehouse;

use App\Filters\Filter;
use Illuminate\Http\Request;

class OpnameStock extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function customer_id($value) {
        return $this->builder->whereHas('item', function($q) use($value) {
            return $q->where('customer_id', $value);
        });
    }

}
