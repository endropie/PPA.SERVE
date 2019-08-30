<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\Request;

class IncomingGood extends Request
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
            $id = $this->incoming_good;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|' : '') .'unique:incoming_goods,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'date' => 'required',
            'time' => 'required',
            'customer_id' => 'required',

            'incoming_good_items.*.item_id' => 'required',

            'incoming_good_items' =>
            function ($attribute, $value, $fail) {
                if (sizeof($value) == 0) {
                    $fail('Incoming-Items must be select min. 1 item production.');
                }
            },
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'incoming_good_items.*.item_id' => $msg,
            'quantity.required'         => $msg,
        ];
    }
}
