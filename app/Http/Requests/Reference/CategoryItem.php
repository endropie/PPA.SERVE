<?php

namespace App\Http\Requests\Reference;

use App\Http\Requests\Request;

class CategoryItem extends Request
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
            $id = $this->category_item;
        } else 
        {
            $id = null;
        }

        return [
            'name' => 'required|string|max:191',
        ];
    }
}
