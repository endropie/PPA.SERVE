<?php
namespace App\Models\Auth;

use Spatie\Permission\Models\Role as BaseRole;
use App\Filters\Filterable;

class Role extends BaseRole {
    use Filterable;
    
}