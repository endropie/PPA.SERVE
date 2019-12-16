<?php
namespace App\Models;
/**********************************************************************************
*   How to use
* =================
 * Collections:
 *  - collect()
 *    Get model to array collection with filterabel, sortable & pagination.
 *
 * - filterable()
 *    Get model to array collection with filterabel on class filter.
 *    for default class filter is route list,
 *    ex: http://namesite/users -> App/Fiters/users::class
 *
 *    Or you can define in model class with:
 *    protected $filterable = (string) name class.
 *
 * - sortable()
 *    Get model to array collection with sortable by request,
 *    name  : 'sort'
 *    order : 'order'
 *
 *
 ***********************************************************************************/

use Illuminate\Support\Facades\Input;
use Request;
use Route;

trait Eloquence
{
    protected $filterableClass = null;

    public function scopeCollect($query, $map = false)
    {
        $request = request();
        $limit = (int) $request->get('limit', 50);
        if($limit == 0) $limit = $query->count();
        $query = $query->paginate($limit);
        return $query;
    }

    public function scopeAll($query, $map = false)
    {
        $request = request();
        $limit = (int) $request->get('limit', null);
        if($limit) {
            $query = $query->limit($limit);
        }
        return $query->get();
    }

}
