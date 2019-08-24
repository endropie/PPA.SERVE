<?php
namespace App\Filters\Common;

use App\Filters\Filter;
use Illuminate\Http\Request;

class Employee extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function has_Job($value = '') {
        return $this->builder->whereHas('employee_job', function($q) use($value){

            return $q->whereNull('id');
        });
    }


}
