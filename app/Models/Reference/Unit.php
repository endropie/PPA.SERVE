<?php
namespace App\Models\Reference;

use App\Filters\Filterable;
use App\Models\Model;

class Unit extends Model
{
    use Filterable;

    protected $fillable = ['code', 'name', 'decimal_in'];

    protected $hidden = ['created_at', 'updated_at'];
}
