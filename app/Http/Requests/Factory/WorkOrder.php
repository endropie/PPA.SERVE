<?php

namespace App\Http\Requests\Factory;

use App\Http\Requests\Request;

class WorkOrder extends Request
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
            $id = $this->work_order;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|' : '') .'unique:work_orders,number,'. $id .',id,revise_number,'. $this->get('revise_number'),
            'line_id' => 'required',
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'work_order_items.*.item_id' => $msg,
            'line_id.required'      => $msg,
        ];
    }
}
