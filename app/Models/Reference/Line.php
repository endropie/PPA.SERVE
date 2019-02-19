<?php

namespace App\Models\Reference;

use App\Models\Model;

class Line extends Model
{
   protected $fillable = ['name', 'description'];

   protected $hidden = ['created_at', 'updated_at'];
}
