<?php
namespace App\Filters\Auth;

use App\Filters\QueryFilters;
use Illuminate\Http\Request;

class Role extends QueryFilters
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }
  
    public function name($value) {
        return $this->builder->where('name',  'like', '%' . $value. '%');
    }

}