<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\Request;

class Item extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Check if store or update
        $method = $this->getMethod();
        
        if ($method == 'PATCH' || $method == 'PUT') {
            $id = $this->item;
        } 
        else $id = null;

        return [
            'code' => 'required|string|max:191|unique:items,NULL,' . $id,
            'name' => 'required|string|max:191',
            'brand_id' => 'required',
            'customer_id' => 'required',
            'specification_id' => 'required',
            
        ];
    }

    public function messages()
    {
        return [
            'color_id.required' => 'Color is required!',
            'color_id.integer' => 'Color is Failed!',
        ];
    }
}
