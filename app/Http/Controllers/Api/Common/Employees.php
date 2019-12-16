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
            $employees = Employee::filter($filters)->all();
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

        return response()->json($employee);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        if ($employee->is_relationship) $this->error("CODE:$employee->code has data relation, Delete not allowed!");

        $employee->delete();

        return response()->json(array_merge($employee->toArray(), ['success' => true]));
    }
}
