<?php
namespace App\Http\Controllers\Api\References;

use App\Http\Requests\Reference\Department as Request;
use App\Http\Controllers\ApiController;
use App\Filters\Filter as Filter;
use App\Models\Reference\Department;

class Departments extends ApiController
{
    public function index(Filter $filters)
    {
        switch (request('mode')) {
            case 'all':
                $departments = Department::filter($filters)->get();
                break;

            case 'datagrid':
                $departments = Department::filter($filters)->get();
                break;

            default:
                $departments = Department::filter($filters)->collect();
                break;
        }

        return response()->json($departments);
    }

    public function store(Request $request)
    {
        $department = Department::create($request->all());

        return response()->json($department);
    }

    public function show($id)
    {
        $department = Department::findOrFail($id);
        $department->append(['has_relationship']);

        return response()->json($department);
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $department->update($request->input());

        return response()->json($department);
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json(['success' => true]);
    }
}
