<?php
namespace App\Filters\Factory;

use App\Filters\QueryFilters;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkOrder extends QueryFilters
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }
  
    public function line_id($value) {
        return $this->builder->where('line_id', $value);
    }

}