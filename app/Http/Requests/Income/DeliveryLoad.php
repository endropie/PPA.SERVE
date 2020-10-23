<?php
namespace App\Http\Requests\Income;

use App\Http\Requests\Request;

class DeliveryLoad extends Request
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
            $id = $this->delivery_load;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required' : 'nullable') .'|unique:delivery_loads,number,'. ($id) .',id',
            'customer_id' => 'required|exists:customers,id',
            'transaction' => 'required|in:REGULER,RETURN',
            'date' => 'required',
            'trip_time' => 'required_if:is_untriped,=,0',
            'vehicle_id' => 'required',
            'delivery_load_items' => 'required|array|min:1',
            'delivery_load_items.*.item_id' => 'required',
            'delivery_load_items.*.quantity' => 'required|gt:0',
            'delivery_load_items.*.unit_id' => 'required',
            'delivery_load_items.*.unit_rate' => 'required',
        ];
    }
}
