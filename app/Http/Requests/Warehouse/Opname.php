<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\Request;

class Opname extends Request
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
            $id = $this->opname;

            if($this->exists('nodata')) return [];
        }
        else {
            $id = null;
            return [];
        }

        return [
            'number' => ($id ? 'required|' : '') .'unique:opnames,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
        ];
    }
}
