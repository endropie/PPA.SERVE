<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\Request;

class Employee extends Request
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
            $id = $this->Employee;
        }
        else $id = null;

        return [
            // 'name' => ($id ? 'required|string|' : '') .'max:191|unique:employees,NULL,' . $id,
        ];
    }
}
