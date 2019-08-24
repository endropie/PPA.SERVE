<?php
namespace App\Filters\Factory;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Production extends Filter
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
