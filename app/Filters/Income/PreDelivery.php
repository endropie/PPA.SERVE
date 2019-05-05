<?php
namespace App\Filters\Income;

use App\Filters\QueryFilters;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PreDelivery extends QueryFilters
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }
  
    public function customer_id($value) {
        return $this->builder->where('customer_id', $value);
    }

}