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
        return $this->builder->whereHas('item', 
          function ($q) use($value) {
            $q->where('customer_id', $value);
        });
    }

    public function sort_part_name($order = 'asc') {
        // return $this->builder->leftJoin('items', 'items.id' , '=', 'ship_delivery_items.item_id')->orderBy('part_name', $order);
        $table = 'ship_delivery_items';
        $field = 'part_name';
        $join = 'items';
        $foreign = 'item_id';

        return $this->builder->select($table.'.*', \DB::raw('(SELECT '.$field.' FROM items WHERE '. $join .'.id ='.$table.'.'.$foreign.' ) as fieldsort'))
            ->orderBy('fieldsort', $order);
    }

}