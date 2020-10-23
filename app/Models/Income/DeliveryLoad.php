<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryLoad extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'transaction', 'order_mode', 'date', 'trip_time', 'is_untriped', 'vehicle_id', 'description',
        'customer_id',  'customer_name', 'customer_phone', 'customer_address', 'customer_note',
    ];


    protected $relationships = [
        'delivery_orders',
    ];

    protected $appends = ['fullnumber'];

    public function delivery_load_items()
    {
        return $this->hasMany('App\Models\Income\DeliveryLoadItem')->withTrashed();
    }

    public function delivery_orders ()
    {
        return $this->hasMany('App\Models\Income\DeliveryOrder');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Reference\Vehicle');
    }

    public function getFullnumberAttribute()
    {
        // if ($this->revise_number) return $this->number ." R.". (int) $this->revise_number;

        return $this->number;
    }
}
