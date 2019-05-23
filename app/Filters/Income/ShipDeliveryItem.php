<?php
namespace App\Filters\Income;

use App\Filters\QueryFilters;
use Illuminate\Http\Request;

class ShipDeliveryItem extends QueryFilters
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }
  
    public function customer_id($value) {
        return $this->builder->whereHas('pre_delivery_item.pre_delivery', 
          function ($q) use($value) {
            $q->where('customer_id', $value);
        });
    }

    public function sort_part_name($name, $order = 'asc') {
        return $this->builder->with(['item'=>
          function ($q) use($name, $order) {
              dd('dd');
            $q->orderBy('part_name', $order);
        }]);
    }

}