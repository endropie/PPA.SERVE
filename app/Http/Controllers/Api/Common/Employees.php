<?php

namespace App\Http\Controllers\Api\Common;

use App\Filters\Common\Employee as Filters;
use App\Http\Requests\Common\Employee as Request;
use App\Http\Controllers\ApiController;
use App\Models\Common\Employee;

class Employees extends ApiController
{
    public function index(Filters $filters)
    {
        switch (request('mode')) {
          case 'all':
            $employees = Employee::with(['department', 'position'])->filter($filters)->get();
          break;

          case 'datagrid':
            $employees = Employee::with(['department', 'position'])->filter($filters)->latest()->get();
          break;

          default:
            $employees = Employee::with(['department', 'position'])->filter($filters)->latest()->collect();
          break;
        }

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $employee = Employee::create($request->all());

        $employee_jobs = $request->employee_jobs;
        for ($i=0; $i < count($employee_jobs); $i++) {
            // create item units on the item updated!
            $employee->jobs()->attach($employee_jobs[$i]['job']);
            // $employee->item_units()->create($employee_jobs[$i]);
        }

        return response()->json($employee);
    }

    public function show($id)
    {
        $employee = Employee::with(['department','position'])->findOrFail($id);

        $employee->setAppends(['has_relationship']);

        return response()->json($employee);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $employee->update($request->input());


        // Delete item units on the item updated!
        $employee->employee_jobs()->delete();
        $unit_rows = $request->employee_jobs;
        for ($i=0; $i < count($unit_rows); $i++) {
            // create item units on the item updated!

            // $employee->employee_jobs()->create($unit_rows[$i]);
        }

        return response()->json($employee);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json(['success' => true]);
    }
}
