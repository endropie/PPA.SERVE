<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Request as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Filter;
use App\Models\Income\ForecastLoad;

class ForecastLoads extends ApiController
{

    public function index(Filter $filters)
    {
        switch (request('mode')) {
            case 'all':
                $forecast_loads = ForecastLoad::filter($filters)->get();
                break;

            case 'datagrid':
                $forecast_loads = ForecastLoad::filter($filters)->latest()->get();

                break;

            default:
                $forecast_loads = ForecastLoad::with(['period'])->filter($filters)->latest()->collect();
                break;
        }

        return response()->json($forecast_loads);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        $request->validate([
            'number' => 'required|string|unique:forecast_loads,number,null,null,period_id,'. request('period_id'),
            'period_id' => 'required|exists:forecast_periods,id'
        ]);

        $forecast_load = ForecastLoad::create($request->all());

        if (!$forecast_load->period->forecast_items->count()) $this->error("Forecast on period is not found!");

        $forecast_load->saveDetail();

        $this->DATABASE::commit();
        return response()->json($forecast_load);
    }

    public function show($id)
    {
        $forecast_load = ForecastLoad::with(['period', 'forecast_load_items'])->findOrFail($id);
        $forecast_load->forecast_load_items->map(function($row) {
            $item = \App\Models\Common\Item::find($row->item_id);
            $line = \App\Models\Reference\Line::find($row->line_id);
            $row->line_name = $line->name;
            $row->item_code = $item->code;
            $row->item_part_name = $item->part_name;
            $row->item_part_subname = $item->part_subname;
            $row->item_load_type = $item->load_type;
            $row->unit_code = $item->unit->code;
            $row->setHidden(['created_at', 'updated_at']);
            return $row;
        });

        return response()->json($forecast_load);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $forecast_load = ForecastLoad::findOrFail($id);
        $forecast_load->forecast_load_items()->delete();
        $forecast_load->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
