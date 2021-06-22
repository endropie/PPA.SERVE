<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use App\Traits\HasCommentable;

class DeliveryCheckout extends Model
{
    use Filterable, WithUserBy, HasCommentable;

    protected $fillable = [
        'date', 'vehicle_id', 'rute_id', 'description'
    ];

    protected $appends = ['fullnumber'];

    protected $casts = [
        'rute_amount' => 'double',
    ];

    public function delivery_loads()
    {
        return $this->hasMany('App\Models\Income\DeliveryLoad')->withTrashed();
    }

    public function delivery_order_internals()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrder')->withTrashed()->whereNull('delivery_load_id');
    }

    public function deportation_goods()
    {
        return $this->hasMany('App\Models\Warehouse\DeportationGood')->withTrashed();
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Reference\Vehicle');
    }

    public function rute()
    {
        return $this->belongsTo('App\Models\Common\Rute');
    }

    public function getFullnumberAttribute ()
    {
        return str_pad($this->id, 5, '0', STR_PAD_LEFT) ;
    }
}
