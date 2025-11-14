<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RbacController extends Controller
{
    // ---------- Helpers ----------
    protected function flushPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // ---------- ROLES (LIST) ----------
    // GET /rbac/roles  (alias to keep existing routes working)
    public function roles(Request $request)
    {
        return $this->indexRoles($request);
    }

    // GET /rbac/roles
    public function indexRoles(Request $request)
    {
        $q = Role::query()->orderBy('name');

        if ($s = trim((string)$request->get('search', ''))) {
            $q->where('name', 'like', "%{$s}%");
        }

        $perPage = (int)($request->get('perPage', 10)) ?: 10;
        $roles = $q->paginate($perPage);

        // include permissions (id,name) for each role row
        $items = array_map(function ($r) {
            $r->setRelation('permissions', $r->permissions()->select('id','name')->get());
            return $r;
        }, $roles->items());

        return response()->json([
            'data'  => $items,
            'total' => $roles->total(),
        ]);
    }

    // ---------- ROLES (CRUD) ----------
    // POST /rbac/roles
    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255','unique:roles,name'],
            'guard' => ['nullable','string'],
        ]);

        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => $data['guard'] ?? config('auth.defaults.guard', 'web'),
        ]);

        $this->flushPermissionCache();

        return response()->json($role, 201);
    }

    // GET /rbac/roles/{role}
    public function showRole(Role $role)
    {
        $role->load('permissions:id,name');
        return response()->json($role);
    }

    // PUT/PATCH /rbac/roles/{role}
    public function updateRole(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255', Rule::unique('roles','name')->ignore($role->id)],
            'guard' => ['nullable','string'],
        ]);

        $role->update([
            'name'       => $data['name'],
            'guard_name' => $data['guard'] ?? $role->guard_name,
        ]);

        $role->load('permissions:id,name');
        $this->flushPermissionCache();

        return response()->json($role);
    }

    // DELETE /rbac/roles/{role}
    public function destroyRole(Role $role)
    {
        $role->delete();
        $this->flushPermissionCache();

        return response()->json(['message' => 'Deleted']);
    }

    // ---------- PERMISSIONS (LIST/CRUD) ----------
    // GET /rbac/permissions
    public function indexPermissions(Request $request)
    {
        $q = Permission::query()->orderBy('name');

        if ($s = trim((string)$request->get('search', ''))) {
            $q->where('name', 'like', "%{$s}%");
        }

        $perPage = (int)($request->get('perPage', 10)) ?: 10;
        $perms = $q->paginate($perPage);

        return response()->json([
            'data'  => $perms->items(),
            'total' => $perms->total(),
        ]);
    }

    // POST /rbac/permissions
    public function storePermission(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:255','unique:permissions,name'],
            'guard_name' => ['nullable','string'],
        ]);

        $perm = Permission::create([
            'name'       => $data['name'],
            'guard_name' => $data['guard_name'] ?? config('auth.defaults.guard', 'web'),
        ]);

        $this->flushPermissionCache();

        return response()->json($perm, 201);
    }

    // DELETE /rbac/permissions/{permission}
    public function destroyPermission(Permission $permission)
    {
        $permission->delete();
        $this->flushPermissionCache();

        return response()->json(['message' => 'Deleted']);
    }

    // ---------- ROLE <-> PERMISSIONS ----------
    // POST /rbac/roles/{role}/permissions/sync
    public function syncRolePermissions(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions'   => ['array'],
            'permissions.*' => ['string'], // names
        ]);

        $names = $data['permissions'] ?? [];

        // Ensure all exist (auto-create missing)
        $guard = $role->guard_name ?? config('auth.defaults.guard', 'web');
        foreach ($names as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => $guard]);
        }

        $role->syncPermissions($names);

        $this->flushPermissionCache();

        $role->load('permissions:id,name');
        return response()->json([
            'message'     => 'Permissions synced',
            'role'        => $role,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }

    // ---------- USER <-> ROLES (optional but handy) ----------
    // POST /rbac/users/{user}/roles/sync
    public function syncUserRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles'   => ['array'],
            'roles.*' => ['string', Rule::exists('roles','name')],
        ]);

        $user->syncRoles($data['roles'] ?? []);
        $this->flushPermissionCache();

        return response()->json([
            'user'  => $user->id,
            'roles' => $user->roles()->pluck('name'),
        ]);
    }
}
