<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:users.update'); // adjust if you prefer
    // }

    public function index()
    {
        return response()->json(Permission::query()->orderBy('name')->get(['id','name']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:150','unique:permissions,name'],
            'guard_name' => ['nullable','string','max:50'],
        ]);

        $perm = Permission::create([
            'name'       => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        return response()->json($perm, 201);
    }

    public function show(Permission $permission)
    {
        return response()->json($permission);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:150', Rule::unique('permissions','name')->ignore($permission->id)],
            'guard_name' => ['nullable','string','max:50'],
        ]);

        $permission->update([
            'name'       => $data['name'],
            'guard_name' => $data['guard_name'] ?? $permission->guard_name,
        ]);

        return response()->json($permission->fresh());
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
