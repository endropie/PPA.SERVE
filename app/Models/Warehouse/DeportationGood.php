<?php
namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Filters\Filterable;
use App\Traits\HasCommentable;

class DeportationGood extends Model
{
    use Filterable, SoftDeletes, WithUserBy, HasCommentable;

    protected $fillable = [
        'number', 'date', 'customer_id', 'description', 'revise_number'
    ];

    protected $appends = ['fullnumber'];

    protected $relationships = [
        'delivery_checkout'
    ];

    public function deportation_good_items()
    {
        return $this->hasMany('App\Models\Warehouse\DeportationGoodItem')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function delivery_checkout()
    {
        return $this->belongsTo('App\Models\Income\DeliveryCheckout');
    }

    public function getFullnumberAttribute()
    {
        if ($this->revise_number) return $this->number ." R.". (int) $this->revise_number;

        return $this->number;
    }
}
