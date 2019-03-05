<?php

namespace App\Http\Requests\Factory;

use App\Http\Requests\Request;

class PackingItem extends Request
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
            $id = $this->packing_item;
        } 
        else $id = null;

        return [
            'number' => ($id ? 'required|string|' : '') .'max:191|unique:packing_items,NULL,' . $id,
            'customer_id' => 'required',
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'packing_item_faults.*.item_id' => $msg,
            'customer_id.required'      => $msg,
        ];
    }
}
