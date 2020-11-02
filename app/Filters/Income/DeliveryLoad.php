<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Deliveryload extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function begin_date($value) {
        return $this->builder->where('date', '>=',  $value);
    }

    public function until_date($value) {
        return $this->builder->where('date', '<=',  $value);
    }

    public function has_checkout($value = '')
    {
        if (!strlen($value)) return $this->builder;
        return $this->builder->when((boolean) $value,
            function ($q) {
                abort('502', 'INI TRUE');
                return $q->whereNotNull('delivery_checkout_id');
            },
            function ($q) {
                return $q->whereNull('delivery_checkout_id');
            });
    }
}
