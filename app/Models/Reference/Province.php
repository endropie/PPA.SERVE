<?php
namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Province extends Model
{
    use Filterable;

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];
}
