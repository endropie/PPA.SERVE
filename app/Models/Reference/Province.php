<?php

namespace App\Models\Reference;

use App\Models\Model;

class Province extends Model
{
   protected $fillable = ['name'];

   protected $hidden = ['created_at', 'updated_at'];
}
