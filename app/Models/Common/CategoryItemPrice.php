<?php
namespace App\Models\Common;

use App\Filters\Filterable;
use App\Models\Model;
use App\Traits\HasCommentable;

class CategoryItemPrice extends Model
{
    use Filterable, HasCommentable;

    protected $fillable = ['customer_id', 'name', 'price', 'description'];

    protected $relationships = ['items'];

    public function customer () {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function items () {
        return $this->hasMany('App\Models\Common\Item');
    }
}
