<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class User extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Check if store or update
        $method = $this->getMethod();
        
        if ($method == 'PATCH' || $method == 'PUT') 
        {
            // $id = $this->user->getAttribute('user');
            $id = $this->user;
            $pass = 'nullable';
        } else 
        {
            $id = null;
            $pass = 'required|confirmed|min:8';
        }

        return [
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,NULL,' . $id,
            'password' => $pass
        ];
    }
}
