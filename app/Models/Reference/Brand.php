<?php

namespace App\Models\Reference;

use App\Models\Model;
use App\Models\Common\Item;
class Brand extends Model
{
   protected $fillable = ['code', 'name', 'description'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $relationships = ['items'];
}
