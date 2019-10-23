<?php

namespace App\Http\Requests\Transport;

use App\Http\Requests\Request;

class ScheduleBoard extends Request
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
            $id = $this->schedule_board;

            if($this->exists('nodata')) return [];
        }
        else $id = null;

        return [
            'number' => ($id ? 'required|string|' : '') .'max:191|unique:schedule_boards,NULL,' . $id,
            'vehicle_id' => 'required',
            'operator_id' => 'required',
            'date' => 'required',
            'time' => 'required',
            'destination' => 'required',
        ];
    }
}
