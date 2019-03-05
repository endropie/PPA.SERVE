<?php

namespace App\Http\Requests\Reference;

use App\Http\Requests\Request;

class Operator extends Request
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
            $id = $this->operator;
        } else 
        {
            $id = null;
        }

        return [
            'name' => ($id ? 'required|string|' : '') .'max:191|unique:operators,NULL,' . $id,
        ];
    }
}
