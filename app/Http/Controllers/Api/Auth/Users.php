<?php
namespace App\Http\Controllers\Api\Auth;

use App\Filters\Auth\User as Filters;
use App\Http\Requests\Auth\User as Request;
use App\Http\Controllers\ApiController;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Hash;

class Users extends ApiController
{
    public function index(Filters $filters)
    {
        switch (request('mode')) {
          case 'all':
            $users = User::filter($filters)->get();
          break;

          case 'datagrid':
            $users = User::orderBy('id','DESC')->filter($filters)->get();

          break;

          default:
            $users = User::filter($filters)->paginate(20);
          break;
        }

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $user->syncRoles($request->has_role);
        $user->syncPermissions($request->has_permission);

        $this->DATABASE::commit();
        return response()->json($user);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        $user->has_permission = $user->getPermissionNames();
        $user->has_role = $user->getRoleNames();

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();
        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        $user->syncRoles($request->has_role);
        $user->syncPermissions($request->has_permission);

        $this->DATABASE::commit();
        return response()->json($user);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();
        $user = User::findOrFail($id);
        $user->delete();

        $this->DATABASE::commit();
        return response()->json(['success' => true]);
    }
}
