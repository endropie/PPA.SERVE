<?php
namespace App\Filters\Factory;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PackingLoad extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function item_id($value) {
        return $this->builder->whereHas('packing_load_items', function($q) use ($value) {
            return $q->where('item_id',  $value);
        });
    }

    public function or_ids($value = '') {
        if (!strlen($value)) return $this->builder;
        $value = explode(',',$value);
        return $this->builder->orWhereIn('id', $value);
    }


}
