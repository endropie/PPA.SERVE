<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class Role extends Request
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
            // $id = $this->user->getAttribute('user');
            $id = $this->role;
            $ruleGuard = 'required';

        } else 
        {
            $id = null;
            $ruleGuard = 'nullable';
        }

        $guard = $this->get('guard_name', 'web');

        return [
            'name' => 'required|unique:auth_roles,NULL,' . $id . ',id,guard_name,'. $guard,
            'guard_name' => $ruleGuard
        ];
    }
}
