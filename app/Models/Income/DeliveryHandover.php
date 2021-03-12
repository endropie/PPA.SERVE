<?php
namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryHandover extends Model
{
    use Filterable, WithUserBy, SoftDeletes, HasCommentable;

    protected $fillable = [
        'number', 'date', 'description', 'customer_id'
    ];

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function delivery_orders()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrder');
    }

    public function getFullnumberAttribute()
    {
        return (string) $this->number;
    }
}
