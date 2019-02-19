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
        } 
        else $id = null;

        return [
            'number' => ($id ? 'required|string|' : '') .'max:191|unique:work_orders,NULL,' . $id,
            'customer_id' => 'required',
        ];
    }

    public function messages()
    {
        $msg = 'The field is required!';

        return [
            'work_order_items.*.item_id' => $msg,
            'customer_id.required'      => $msg,
        ];
    }
}
