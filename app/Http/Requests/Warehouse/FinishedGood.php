<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\Request;

class FinishedGood extends Request
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
            $id = $this->finished_good;
        } 
        else $id = null;

        return [
            'number' => 'required|string|max:191|unique:finished_goods,NULL,' . $id,
            'date' => 'required',
            'time' => 'required',
            'customer_id' => 'required',

            'finished_good_items.*.item_id' => 'required',

            'finished_good_items' =>
            function ($attribute, $value, $fail) {
                if (sizeof($value) == 0) {
                    $fail('Finished-Items must be select min. 1 item production.');
                }
            },
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'finished_good_items.*.item_id' => $msg,
            'customer_id.required'      => $msg,
        ];
    }
}
