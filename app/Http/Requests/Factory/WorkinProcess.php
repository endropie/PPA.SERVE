<?php

namespace App\Http\Requests\Factory;

use App\Http\Requests\Request;

class WorkinProcess extends Request
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
            $id = $this->workin_process;
        } 
        else $id = null;

        return [
            'number' => 'required|string|max:191|unique:workin_processes,NULL,' . $id,
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
            'customer_id' => 'required',

            'workin_process_items.*.item_id' => 'required',

            'workin_process_items' =>
            function ($attribute, $value, $fail) {
                if (sizeof($value) == 0) {
                    $fail('Wrok-in Process Items must be select min. 1 item production.');
                }
            },
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'workin_process_items.*.item_id' => $msg,
            'customer_id.required'      => $msg,
        ];
    }
}
