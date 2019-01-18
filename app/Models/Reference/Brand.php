<?php

namespace App\Models\Reference;

use App\Models\Model;
use App\Models\Common\Item;
class Brand extends Model
{
   protected $fillable = ['name', 'description'];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = ['items'];

   public function items()
   {
      return $this->hasMany(Item::class);
   }

   
}
