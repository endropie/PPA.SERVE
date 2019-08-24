<?php

namespace App\Http\Requests\Factory;

use App\Http\Requests\Request;

class Production extends Request
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
            $id = $this->production;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|string|' : '') .'max:191|unique:productions,NULL,' . $id,
            'line_id' => 'required',
        ];
    }
}
