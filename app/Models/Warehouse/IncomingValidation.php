<?php

namespace App\Models\Warehouse;

use App\Models\Model;
use App\Models\WithUserBy;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomingValidation extends Model
{
    use WithUserBy, SoftDeletes;

    protected $fillable = ['date', 'description'];

    public function incoming_validation_items()
    {
        return $this->hasMany(\App\Models\Warehouse\IncomingValidationItem::class);
    }
}
