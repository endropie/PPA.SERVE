<?php

namespace App\Http\Requests\Factory;

use App\Http\Requests\Request;

class WorkProduction extends Request
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
            $id = $this->work_production;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|' : '') .'max:191|unique:work_productions,NULL,' . $id,
            'line_id' => 'required',
            'date' => 'required',
            'stockist' => 'required',
            'shift_id' => 'required'
        ];
    }
}
