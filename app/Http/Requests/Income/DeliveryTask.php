<?php
namespace App\Http\Requests\Income;

use App\Http\Requests\Request;

class DeliveryTask extends Request
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
            $id = $this->delivery_task;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required' : 'nullable') .'|unique:delivery_tasks,number,'. ($id) .',id',
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required',
            'transaction' => 'required|in:REGULER,RETURN',
            'rit' => 'required',
            'delivery_task_items.*.item_id' => 'required',
            'delivery_task_items.*.quantity' => 'required',
            'delivery_task_items.*.unit_id' => 'required',
            'delivery_task_items.*.unit_rate' => 'required',

            'delivery_task_items' =>
            function ($attribute, $value, $fail) {
                if (sizeof($value) == 0) {
                    $fail('Delivery-Task-Items must be select min. 1 item.');
                }
            },
        ];
    }
}
