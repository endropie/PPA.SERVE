<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\Request;

class Transport extends Request
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
            $id = $this->transport;
        } 
        else $id = null;

        return [
            'number' => ($id ? 'string|' : '') .'max:191|unique:transports,NULL,' . $id,
            'date' => '',
            'time' => '',
            'vehicle_id' => 'required',
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'vehicle_id.required'         => $msg,
        ];
    }
}
