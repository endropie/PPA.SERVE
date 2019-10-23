<?php

namespace App\Models\Factory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class PackingItemFault extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fault_id', 'quantity',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'quantity' => 'double'
    ];

    protected $relationships = [];

    public function packing_item()
    {
        return $this->belongsTo('App\Models\Factory\PackingItem');
    }

    public function fault()
    {
        return $this->belongsTo('App\Models\Reference\Fault');
    }

}
