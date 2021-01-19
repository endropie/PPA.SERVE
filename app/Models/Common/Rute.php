<?php
namespace App\Models\Common;

use App\Filters\Filterable;
use App\Models\Model;
use App\Traits\HasCommentable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rute extends Model
{
    use Filterable, HasCommentable, SoftDeletes;

    protected $fillable = ['name', 'description'];

    protected $hidden = ['updated_at'];

    protected $relationships = ['delivery_checkouts'];

    public function rute_customers() {
        return $this->hasMany('App\Models\Common\RuteCustomer');
    }

    public function delivery_checkouts() {
        return $this->hasMany('App\Models\Income\DeliveryCheckout');
    }
}
