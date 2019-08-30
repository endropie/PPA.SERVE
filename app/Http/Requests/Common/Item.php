<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\Request;

class Item extends Request
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
            $id = $this->item;
            $code = 'required|';
        }
        else {
            $id = null;
            $code = '';
        }


        return [
            'code' => ($id ? 'required|':'').'max:191|unique:items,NULL,' . $id,
            'brand_id' => 'required',
            'customer_id' => 'required',
            'specification_id' => 'required',
            'item_productions.*.production_id' => 'required',

            'item_productions' =>
            function ($attribute, $value, $fail) {
                if (sizeof($value) == 0) {
                    $fail('Pre productions must be select min. 1 process production.');
                }
            },
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'item_productions.*.production_id' => $msg,
            'brand_id.required'         => $msg,
            'customer_id.required'      => $msg,
            'specification_id.required' => $msg,
        ];
    }
}
