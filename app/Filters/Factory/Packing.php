<?php
namespace App\Filters\Factory;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Packing extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function item_id($value) {
        return $this->builder->whereHas('packing_items', function($item) use ($value) {
            return $item->where('item_id',  $value);
        });
    }
}
