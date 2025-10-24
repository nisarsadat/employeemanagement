<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RbacController extends Controller
{
    // ------- Roles -------
    public function indexRoles()
    {
        return Role::orderBy('name')->get();
    }

    public function storeRole(Request $request, PermissionRegistrar $registrar)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100','unique:roles,name'],
        ]);
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $registrar->forgetCachedPermissions();
        return response()->json($role, 201);
    }

    public function updateRole($id, Request $request, PermissionRegistrar $registrar)
    {
        $role = Role::findOrFail($id);
        $data = $request->validate([
            'name' => ['required','string','max:100', Rule::unique('roles','name')->ignore($role->id)],
        ]);
        $role->update(['name' => $data['name']]);
        $registrar->forgetCachedPermissions();
        return $role;
    }

    public function destroyRole($id, PermissionRegistrar $registrar)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        $registrar->forgetCachedPermissions();
        return response()->noContent();
    }

    // ------- Permissions -------
    public function indexPermissions()
    {
        return Permission::orderBy('name')->get();
    }

    public function storePermission(Request $request, PermissionRegistrar $registrar)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100','unique:permissions,name'],
        ]);
        $perm = Permission::create(['name' => $data['name'], 'guard_name' => 'web']);
        $registrar->forgetCachedPermissions();
        return response()->json($perm, 201);
    }

    public function updatePermission($id, Request $request, PermissionRegistrar $registrar)
    {
        $perm = Permission::findOrFail($id);
        $data = $request->validate([
            'name' => ['required','string','max:100', Rule::unique('permissions','name')->ignore($perm->id)],
        ]);
        $perm->update(['name' => $data['name']]);
        $registrar->forgetCachedPermissions();
        return $perm;
    }

    public function destroyPermission($id, PermissionRegistrar $registrar)
    {
        $perm = Permission::findOrFail($id);
        $perm->delete();
        $registrar->forgetCachedPermissions();
        return response()->noContent();
    }

    // ------- Role permissions -------
    public function rolePermissions($id)
    {
        $role = Role::findOrFail($id);
        return [
            'role' => $role,
            'permissions' => $role->permissions()->pluck('name'),
        ];
    }

    public function syncRolePermissions($id, Request $request, PermissionRegistrar $registrar)
    {
        $role = Role::findOrFail($id);
        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);
        $role->syncPermissions($data['permissions'] ?? []);
        $registrar->forgetCachedPermissions();
        return [
            'role' => $role->name,
            'permissions' => $role->permissions()->pluck('name'),
        ];
    }

    // ------- Users -------
    public function indexUsers(Request $request)
    {
        $users = User::query()
            ->select(['id','name','username','photo_path','active'])
            ->orderBy('name')
            ->get()
            ->map(function (User $u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'photo_url' => $u->photo_url,
                    'active' => (bool) $u->active,
                    'roles' => $u->getRoleNames(),
                    'permissions' => $u->getPermissionNames(),
                ];
            });
        return $users;
    }

    public function syncUserRoles($id, Request $request, PermissionRegistrar $registrar)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string'],
        ]);
        $user->syncRoles($data['roles'] ?? []);
        $registrar->forgetCachedPermissions();
        return [
            'user' => $user->username,
            'roles' => $user->getRoleNames(),
        ];
    }

    public function syncUserPermissions($id, Request $request, PermissionRegistrar $registrar)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);
        $user->syncPermissions($data['permissions'] ?? []);
        $registrar->forgetCachedPermissions();
        return [
            'user' => $user->username,
            'permissions' => $user->getPermissionNames(),
        ];
    }
}
