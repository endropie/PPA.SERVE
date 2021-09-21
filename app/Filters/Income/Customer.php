<?php
namespace App\Filters\Income;

use App\Filters\Filter;
use Illuminate\Http\Request;

class Customer extends Filter
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function trip_date ($value = '') {
        return $this->builder->when(request('trip_futured', false),
            function($q) use($value) {
                return $q->whereHas('customer_trips', function($q) use($value) {
                    $intday = \Carbon\Carbon::parse($value)->dayOfWeekIso;
                    if ($intday) return $q->where('intday', $intday);
                });
            },
            function($q) use($value) {
                return $q->whereHas('trips', function($q) use($value) {
                    if ($value) return $q->where('date', $value);
                });
            }
        );
    }

    public function delivery_date ($value = '') {
        return $this->builder->whereHas('delivery_task_items', function($q) use($value) {
            return $q->whereHas('delivery_task', function($q) use ($value){
                return $q->where('date', $value);
            });
        });
    }

    public function search($value = '')
    {
        if (!strlen($value)) return $this->builder;

        if ($fields = request('search-keys')) {
            $fields = explode(',', $fields);
        } else {

            $tableName = $this->builder->getQuery()->from;
            $fields = \Schema::getColumnListing($tableName);
            $except = [$this->builder->getModel()->getKeyName()];
            $fields = array_diff_key($fields, $except);
        }


        $separator = substr_count($value, '|') > 0 ? '|' : ' ';
        $keywords = explode($separator, $value);
        return $this->builder->where(function ($query) use ($fields, $keywords) {
            foreach ($keywords as $keyword) {
                if (strlen($keyword)) {
                    $query->where(function ($query) use ($fields, $keyword) {
                        foreach ($fields as $field) {
                            $query->orWhere($field, 'like', '%' . $keyword . '%');
                        }
                    });
                }
            }
        });
    }

}
