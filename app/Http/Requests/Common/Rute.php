<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\Request;

class Rute extends Request
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
            $id = $this->rute;
        } else
        {
            $id = null;
        }

        return [
            'name' => ($id ? 'required|string|' : '') .'max:191|unique:rutes,NULL,' . $id,
            'cost' => '',
            'rute_customers' => 'required|array|min:1',
            'rute_customers.*.customer_id' => 'required',
            'rute_customers.*.code' => 'required',
        ];
    }
}
