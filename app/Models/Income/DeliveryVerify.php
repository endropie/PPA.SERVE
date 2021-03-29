<?php
namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryVerify extends Model
{
    use Filterable, SoftDeletes, HasCommentable, WithUserBy;

    protected $fillable = ['customer_id', 'description'];

    public function Delivery_verify_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryVerifyItem');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }
}
