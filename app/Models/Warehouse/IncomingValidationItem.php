<?php

namespace App\Models\Warehouse;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomingValidationItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['quantity'];

    public function incoming_validation()
    {
        return $this->belongsTo(\App\Models\Warehouse\IncomingValidation::class);
    }

    public function incoming_good_item()
    {
        return $this->belongsTo(\App\Models\Warehouse\IncomingGoodItem::class);
    }
}
