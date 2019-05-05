<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class Permission extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Check if store or update
        if ($this->getMethod() == 'PATCH' || $this->getMethod() == 'PUT') 
        {
            // $id = $this->user->getAttribute('id');
            $id = $this->permission;
            $ruleGuard = 'required';

        } else 
        {
            $id = null;
            $ruleGuard = 'nullable';
        }

        $guard = $this->get('guard_name', 'web');

        return [
            'name' => 'required|unique:auth_permissions,NULL,' . $id . ',id,guard_name,'. $guard,
            'guard_name' => $ruleGuard
        ];
    }
}
