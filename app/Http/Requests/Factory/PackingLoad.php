<?php

namespace App\Http\Requests\Factory;

use App\Http\Requests\Request;

class PackingLoad extends Request
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
            $id = $this->packing_load;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|string|' : '') .'max:191|unique:packing_loads,NULL,' . $id,
            // 'customer_id' => 'required',
            'packing_load_items.*.item_id' => 'required|distinct',
            'packing_load_items.*.quantity' => 'required|numeric|min:0',
            'packing_load_items.*.unit_id' => 'required',
            'packing_load_items.*.unit_rate' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'packing_load_items.item_id' => 'Part',
            'packing_load_items.quantity' => 'Quantity',
            'packing_load_items.unit_id' => 'Unit',
            'packing_load_items.unit_rate' => 'Unit rate',
        ];
    }
}
