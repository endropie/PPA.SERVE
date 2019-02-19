<?php

namespace App\Http\Requests\Factory;

use App\Http\Requests\Request;

class Line extends Request
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
            $id = $this->line;
        } else 
        {
            $id = null;
        }

        return [
            'name' => 'required|string|max:191',
        ];
    }
}
