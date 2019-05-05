<?php
namespace App\Filters\Warehouse;

use App\Filters\QueryFilters;
use Illuminate\Http\Request;
use Carbon\Carbon;

class IncomingGood extends QueryFilters
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

    public function date_start($value) {
        return $this->builder->where('date', '>=',  $value);
    }

    public function date_end($value) {
        return $this->builder->where('date', '<=',  $value);
    }

    public function sortBy($value) {
        $order = Request('sortDesc') === "1" ? 'desc' : 'asc';
        switch ($value) 
        {
            case 'customer_id': return $this->builder->with([
                'customer' => function($query) use($order) {
                    $query->where('id', 1);
                    // dd ($query);
                }
              ]);        
            break;
            
            default:return $this->builder->orderBy($value, $order);
            break;
        }

        
        
    }

}