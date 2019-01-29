<?php

namespace App\Models\Reference;

use App\Models\Model;

class Size extends Model
{
   protected $fillable = ['code', 'name'];

   protected $hidden = ['created_at', 'updated_at'];
}
