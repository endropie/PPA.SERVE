<?php

namespace App\Http\Requests\Reference;

use App\Http\Requests\Request;

class Specification extends Request
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
            $id = $this->specification;
        } 
        else $id = null;

        return [
            'code' => 'required|string|max:191|unique:specifications,NULL,' . $id,
            'name' => 'required|string|max:191',
            'color_id' => 'required',
            'times_spray_white' => 'required',
            'times_spray_red' => 'required',
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
