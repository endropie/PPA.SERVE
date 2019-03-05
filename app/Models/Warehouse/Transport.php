<?php

namespace App\Models\Warehouse;

use App\Models\Model;

class Transport extends Model
{
   protected $fillable = ['number', 'date', 'time', 'mode', 'vehicle_id', 'plan_date', 'plan_time', 'description'];

   protected $hidden = ['created_at', 'updated_at'];
}
