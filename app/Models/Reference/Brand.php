<?php
namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Brand extends Model
{
    use Filterable;

    protected $fillable = ['code', 'name', 'description'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $relationships = [];
}
