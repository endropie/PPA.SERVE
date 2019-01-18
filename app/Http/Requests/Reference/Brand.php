<?php

namespace App\Http\Requests\Reference;

use App\Http\Requests\Request;

class Brand extends Request
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
            // 'number' => 'required|unique:accounts,NULL,' . $id,
            'name' => 'required|string|max:191',
        ];
    }
}
