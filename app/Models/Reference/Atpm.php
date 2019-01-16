<?php

namespace App\Models\Reference;

use App\Models\Model;

class Atpm extends Model
{
   protected $table = "atpm";

   protected $fillable = ['name', 'description'];

   protected $hidden = ['created_at', 'updated_at'];
}
