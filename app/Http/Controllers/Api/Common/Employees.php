<?php

namespace App\Http\Controllers\Api\Common;

use App\Filters\Common\Employee as Filters;
use App\Http\Requests\Common\Employee as Request;
use App\Http\Controllers\ApiController;
use App\Models\Auth\User;
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

        $this->setUser($employee, $request);

        return response()->json($employee);
    }

    public function show($id)
    {
        $employee = Employee::with(['department','position','user'])->findOrFail($id);

        $employee->append(['has_relationship']);

        return response()->json($employee);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $employee->update($request->input());

        $this->setUser($employee, $request);

        return response()->json($employee);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        if ($employee->is_relationship) $this->error("CODE:$employee->code has data relation, Delete not allowed!");

        $employee->delete();

        return response()->json(array_merge($employee->toArray(), ['success' => true]));
    }

    protected function setUser ($employee, $request) {
        if($setup = $request->setup_user) {
            if ($user = $employee->user) {
                $user->update([
                    'password' => bcrypt($setup['password']),
                ]);
                $user->save();
            }
            else {
                $user = User::create([
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'password' => bcrypt($setup['password']),
                ]);
                $employee->user()->associate($user);
                $employee->save();
            }
        }
    }
}
