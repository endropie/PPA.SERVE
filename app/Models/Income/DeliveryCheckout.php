<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;

class DeliveryCheckout extends Model
{
    use Filterable, WithUserBy;

    protected $fillable = [
        'date', 'vehicle_id', 'description'
    ];

    protected $appends = ['fullnumber'];

    public function delivery_loads()
    {
        return $this->hasMany('App\Models\Income\DeliveryLoad')->withTrashed();
    }

    public function delivery_order_internals()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrder')->withTrashed();
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Reference\Vehicle');
    }

    public function getFullnumberAttribute ()
    {
        return str_pad($this->id, 5, '0', STR_PAD_LEFT) ;
            // preg_replace('/\s+/', '', $this->vehicle->number)
            // ."/". date_format(date_create($this->date), 'y.m.d');
    }
}
