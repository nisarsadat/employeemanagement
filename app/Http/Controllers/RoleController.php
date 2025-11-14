<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:users.update'); // adjust to your policy
    // }

    /**
     * List roles with a count of their permissions
     */
    public function index()
    {
        // returns: [{ id, name, permissions_count }, ...]
        $roles = Role::query()
            ->select('id', 'name')
            ->withCount('permissions') // adds permissions_count
            ->orderBy('name')
            ->get();

        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:100','unique:roles,name'],
            'guard_name' => ['nullable','string','max:50'],
            // optionally allow initial permissions:
            'permissions'   => ['sometimes','array'],
            'permissions.*' => ['string'],
        ]);

        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        // return with permissions_count too
        $role->loadCount('permissions');

        return response()->json($role, 201);
    }

    public function show(Role $role)
    {
        $role->loadCount('permissions');

        return response()->json([
            'id'                => $role->id,
            'name'              => $role->name,
            'permissions_count' => $role->permissions_count,
            // optional: include its permissions
            'permissions'       => $role->permissions()->pluck('name'),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'         => ['required','string','max:100', Rule::unique('roles','name')->ignore($role->id)],
            'guard_name'   => ['nullable','string','max:50'],
            'permissions'  => ['sometimes','array'],
            'permissions.*'=> ['string'],
        ]);

        $role->update([
            'name'       => $data['name'],
            'guard_name' => $data['guard_name'] ?? $role->guard_name,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        // include permissions_count in the fresh response
        $role->loadCount('permissions');

        return response()->json($role->fresh());
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
