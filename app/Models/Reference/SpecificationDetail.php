<?php
namespace App\Models\Reference;

use App\Models\Model;

class SpecificationDetail extends Model
{
    protected $fillable = [
        'thick', 'plate'
    ];

    protected $hidden = ['created_at', 'updated_at'];

}
