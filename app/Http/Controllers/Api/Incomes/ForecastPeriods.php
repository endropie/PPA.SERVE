<?php

namespace App\Http\Controllers\Api\Incomes;

use App\Http\Requests\Request as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Filter;
use App\Models\Income\ForecastPeriod;

class ForecastPeriods extends ApiController
{

    public function index(Filter $filters)
    {
        switch (request('mode')) {
            case 'all':
                $forecast_periods = ForecastPeriod::filter($filters)->get();
                break;

            case 'datagrid':
                $forecast_periods = ForecastPeriod::filter($filters)->latest()->get();

                break;

            default:
                $forecast_periods = ForecastPeriod::filter($filters)->latest()->collect();
                break;
        }

        return response()->json($forecast_periods);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        $request->validate([
            'period' => 'required|date|unique:forecast_periods,period',
            'days' => 'required'
        ]);

        $forecast_period = ForecastPeriod::create($request->all());

        $this->DATABASE::commit();
        return response()->json($forecast_period);
    }

    public function show($id)
    {
        $forecast_period = ForecastPeriod::with(['customer'])->withTrashed()->findOrFail($id);
        $forecast_period->is_editable = (!$forecast_period->is_related);

        return response()->json($forecast_period);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $forecast_period = ForecastPeriod::findOrFail($id);

        $forecast_period->update($request->input());

        $this->DATABASE::commit();
        return response()->json($forecast_period);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $forecast_period = ForecastPeriod::findOrFail($id);
        $forecast_period->forecast_load()->delete();
        $forecast_period->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
