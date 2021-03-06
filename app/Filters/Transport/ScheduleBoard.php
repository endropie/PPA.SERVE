<?php
namespace App\Filters\Transport;

use App\Filters\Filter;
use Illuminate\Http\Request;

class ScheduleBoard extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function begin_date($value) {
        return $this->builder->where('date', '>=',  $value);
    }

    public function until_date($value) {
        return $this->builder->where('date', '<=',  $value);
    }

    // public function customer_in($value) {
    //     $value = explode(',', $value);
    //     return $this->builder->whereHas('customers', function($query) use($value) {
    //         return $query->where('customer_id', $value);
    //     });
    // }
}
