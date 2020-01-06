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
            // 'user_id' => 'nullable|integer|unique:employees,NULL,' . $id,
        ];
    }
}
