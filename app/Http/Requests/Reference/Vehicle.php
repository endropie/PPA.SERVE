<?php

namespace App\Http\Requests\Reference;

use App\Http\Requests\Request;

class Vehicle extends Request
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
            $id = $this->vehicle;
        } else
        {
            $id = null;
        }

        return [
            'number' => ($id ? 'required|string|' : '') .'max:191|unique:vehicles,NULL,' . $id,
            'owner' => 'required',
            'type' => 'required',
            // 'department_id' => 'required',
        ];
    }
}
