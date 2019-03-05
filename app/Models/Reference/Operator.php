<?php

namespace App\Models\Reference;

use App\Models\Model;

class Operator extends Model
{
   protected $fillable = [
      'name', 'phone'
   ];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = [];
   
}
 