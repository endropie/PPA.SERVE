<?php

namespace App\Models\Income;

use App\Filters\Filterable;
use App\Models\Model;
use App\Models\WithUserBy;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryInternal extends Model
{
    use Filterable, SoftDeletes, WithUserBy;

    protected $fillable = [
        'number', 'date', 'option', 'internal_notes',
        'customer_id', 'customer_name', 'customer_phone', 'customer_address'
    ];

    protected $casts = [
        'option' => 'array'
    ];

    public function customer()
    {
        return $this->belongsTo('App\Models\Income\Customer');
    }
}
