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
                $forecasts = Forecast::with(['customer'])->collect();                
                break;
        }

        return response()->json($forecasts);
    }

    public function store(Request $request)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        if(!$request->number) $request->merge(['number'=> $this->getNextForecastNumber()]);

        $forecast = Forecast::create($request->all());

        $item = $request->forecast_items;
        for ($i=0; $i < count($item); $i++) { 

            // create item production on the incoming Goods updated!
            $forecast->forecast_items()->create($item[$i]);
        }

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
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
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $forecast = Forecast::findOrFail($id);

        $forecast->update($request->input());

        // Delete old incoming goods items when $request detail rows has not ID
        $ids =  array_filter((array_column($request->forecast_items, 'id')));
        $delete_details = $forecast->forecast_items()->whereNotIn('id', $ids)->get();
        
        if($delete_details) {
          foreach ($delete_details as $detail) {
            // Delete detail of "Request Order"
            $detail->delete();
          }
        }

        $rows = $request->forecast_items;
        for ($i=0; $i < count($rows); $i++) { 
            $row = $rows[$i];
            $detail = $forecast->forecast_items()->find($row['id']);
            if($detail) {                
                // update item row on the request orders updated!
                $detail->update($row);
            }
            else{
                // create item row on the request orders updated!
                $forecast->forecast_items()->create($row);
            }
        }

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json($forecast);
    }

    public function destroy($id)
    {
        // DB::beginTransaction => Before the function process!
        $this->DATABASE::beginTransaction();

        $forecast = Forecast::findOrFail($id);
        $forecast->forecast_items()->delete();
        $forecast->delete();

        // DB::Commit => Before return function!
        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
