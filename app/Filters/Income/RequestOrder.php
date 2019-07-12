<?php
namespace App\Filters\Income;

use App\Filters\QueryFilters;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RequestOrder extends QueryFilters
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }
  
    public function customer_id($value) {
        abort(501, $value);
        return $this->builder->where('customer_id', $value);
    }

    public function begin_date($value) {
        return $this->builder->where('date', '>=',  $value);
    }

    public function until_date($value) {
        return $this->builder->where('date', '<=',  $value);
    }

    public function sortBy($value) {
        $order = Request('sortDesc') === "1" ? 'desc' : 'asc';
        switch ($value) 
        {
            case 'customer_id': return $this->builder->with([
                'customer' => function($query) use($order) {
                    // $query->where('id', 1);
                    // dd ($query);
                }
              ]);        
            break;
            
            default:return $this->builder->orderBy($value, $order);
            break;
        }

        
        
    }

    // public function search($value) {
    //     // dd($this);
    //     // dd($this->builder->model);
    //     return $this->builder; //->where('id', $value);
    // }

}