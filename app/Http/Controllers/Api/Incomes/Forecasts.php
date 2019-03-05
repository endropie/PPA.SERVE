<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Income\Forecast as Request;
use App\Http\Controllers\ApiController;

use App\Models\Income\Forecast; 
use App\Traits\GenerateNumber;

class Forecasts extends ApiController
{
    use GenerateNumber;

    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $forecasts = Forecast::filterable()->get();    
                break;

            case 'datagrid':    
                $forecasts = Forecast::with(['customer'])->filterable()->get();
                
                break;

            default:
                $forecasts = Forecast::collect();                
                break;
        }

        return response()->json($forecasts);
    }

    public function store(Request $request)
    {
        if(!$request->number) $request->merge(['number'=> $this->getNextForecastNumber()]);

        $forecast = Forecast::create($request->all());

        $item = $request->forecast_items;
        for ($i=0; $i < count($item); $i++) { 

            // create item production on the incoming Goods updated!
            $forecast->forecast_items()->create($item[$i]);
        }

        return response()->json($forecast);
    }

    public function show($id)
    {
        $forecast = Forecast::with(['forecast_items.item.item_units', 'forecast_items.unit'])->findOrFail($id);
        $forecast->is_editable = (!$forecast->is_related);

        return response()->json($forecast);
    }

    public function update(Request $request, $id)
    {
        $forecast = Forecast::findOrFail($id);

        $forecast->update($request->input());

        // Delete items on the incoming goods updated!
        $forecast->forecast_items()->delete();

        $item = $request->forecast_items;
        for ($i=0; $i < count($item); $i++) { 

            // create item row on the incoming Goods updated!
            $forecast->forecast_items()->create($item[$i]);
        }

        return response()->json($forecast);
    }

    public function destroy($id)
    {
        $forecast = Forecast::findOrFail($id);
        $forecast->delete();

        return response()->json(['success' => true]);
    }
}
