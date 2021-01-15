<?php
namespace App\Filters\Common;

use App\Filters\Filter;
use Illuminate\Http\Request;

class Rute extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function customers($value = '')
    {
        if (!strlen($value)) return $this->builder;
        $customers = explode(',', $value);
        return $this->builder
            ->whereHas('rute_customers', function($q) use($customers){
                return $q->whereIn('customer_id', $customers);
            })
            ->whereDoesntHave('rute_customers', function($q) use($customers){
                return $q->whereNotIn('customer_id', $customers);
            })
            ->WhereRaw('(SELECT COUNT(DISTINCT customer_id) FROM `rute_customers` WHERE `rutes`.`id` = `rute_customers`.`rute_id`) = '. count($customers));
    }


}
