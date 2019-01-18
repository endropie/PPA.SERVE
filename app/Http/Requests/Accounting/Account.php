<?php

namespace App\Http\Requests\Accounting;

use App\Http\Requests\Request;

class Account extends Request
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
            // $id = $this->user->getAttribute('account');
            $id = $this->account;
        } else 
        {
            $id = null;
        }

        return [
            'name' => 'required|string|max:191',
            'number' => 'required|unique:accounts,NULL,' . $id,
        ];
    }
}
