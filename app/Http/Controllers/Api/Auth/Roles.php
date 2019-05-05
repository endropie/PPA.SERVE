<?php
namespace App\Http\Controllers\Api\Auth;

use App\Filters\Auth\Role as Filters;
use App\Http\Requests\Auth\Role as Request;
use App\Http\Controllers\ApiController;
use App\Models\Auth\Role;

class Roles extends ApiController
{
    public function index(Filters $filters)
    {
        switch (request('mode')) {
          case 'all':            
            $roles = Role::filter($filters)->get();    
          break;

          case 'datagrid':
            $roles = Role::orderBy('id','DESC')->filter($filters)->get();
            
          break;

          default:
            $roles = Role::filter($filters)->paginate(20);     
          break;
        }

        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web'
        ]);

        $role->syncPermissions($request->has_permission);

        $this->DATABASE::commit();
        return response()->json($role);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $role->has_permission = $role->permissions->pluck(['name']);
        
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();
        $role = Role::findOrFail($id);

        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name
        ]);

        $role->syncPermissions($request->has_permission);

        $this->DATABASE::commit();
        return response()->json($role);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();
        $role = Role::findOrFail($id);
        $role->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
