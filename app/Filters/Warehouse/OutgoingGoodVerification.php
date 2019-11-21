<?php
namespace App\Filters\Warehouse;

use App\Filters\Filter;
use Illuminate\Http\Request;

class OutgoingGoodVerification extends Filter
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
        // return $this->builder->leftJoin('items', 'items.id' , '=', 'outgoing_good_items.item_id')->orderBy('part_name', $order);
        $table = 'outgoing_good_verifications';
        $field = 'part_name';
        $join = 'items';
        $foreign = 'item_id';

        return $this->builder->select($table.'.*', \DB::raw('(SELECT '.$field.' FROM items WHERE '. $join .'.id ='.$table.'.'.$foreign.' ) as fieldsort'))
            ->orderBy('fieldsort', $order);
    }

    public function unvalidated($value = null) {
        if($value == 'true' || $value == '1') {
            return $this->builder->whereNull('validated_at');
        }

        return $this->builder;
    }

}
