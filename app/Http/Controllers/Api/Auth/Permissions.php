<?php
namespace App\Http\Controllers\Api\Auth;

use App\Filters\Auth\Permission as Filters;
use App\Http\Requests\Auth\Permission as Request;
use App\Http\Controllers\ApiController;
use App\Models\Auth\Permission;

class Permissions extends ApiController
{
    public function index(Filters $filters)
    {
        switch (request('mode')) {
          case 'all':            
            $permissions = Permission::filter($filters)->get();    
          break;

          case 'datagrid':
            $permissions = Permission::orderBy('id','DESC')->filter($filters)->get();
            
          break;

          default:
            $permissions = Permission::filter($filters)->paginate(20);     
          break;
        }

        return response()->json($permissions);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        
        $permission = Permission::create([
          'name' => $request->name,
          'guard_name' => $request->guard_name ?? 'web'
      ]);
        
        $this->DATABASE::commit();
        return response()->json($permission);

    }

    public function show($id)
    {
        $permission = Permission::findOrFail($id);

        return response()->json($permission);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();
        $permission = Permission::findOrFail($id);

        $permission->update([
          'name' => $request->name,
          'guard_name' => $request->guard_name
      ]);

        $this->DATABASE::commit();
        return response()->json($permission);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();
        
        $permission = Permission::findOrFail($id);
        $permission->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
