<?php

namespace App\Models\Factory;

use App\Models\Model;

class Production extends Model
{
   protected $fillable = ['name', 'description'];

   protected $hidden = ['created_at', 'updated_at'];
}
