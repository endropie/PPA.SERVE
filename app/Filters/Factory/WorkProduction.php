<?php
namespace App\Filters\Factory;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkProduction extends Filter
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

    public function item_id($value) {
        return $this->builder->whereHas('work_production_items', function($q) use($value) {
            return $q->where('item_id', $value);
        });
    }

}
