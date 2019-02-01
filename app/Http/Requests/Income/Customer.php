<?php

namespace App\Http\Requests\Income;

use App\Http\Requests\Request;

class Customer extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $method = $this->getMethod();
        
        if ($method == 'PATCH' || $method == 'PUT') {
            $id = $this->cusomer;
        } 
        else $id = null;

        return [
            'code' => 'required|string|max:191|unique:items,NULL,' . $id,
            'name' => 'required|string|max:191',
            
        ];
    }

    public function messages()
    {
        return [
            // Code..
        ];
    }
}
