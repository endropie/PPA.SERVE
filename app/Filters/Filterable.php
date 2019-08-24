<?php
Namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use App\Filters\Filter;

trait Filterable
{
    public function scopeFilter($query, Filter $filters)
    {
        return $filters->apply($query);
    }
}
