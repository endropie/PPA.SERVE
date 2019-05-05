<?php
namespace App\Models\Auth;

use Spatie\Permission\Models\Permission as BasePermission;
use App\Filters\Filterable;

class Permission extends BasePermission {
    use Filterable;
    
}