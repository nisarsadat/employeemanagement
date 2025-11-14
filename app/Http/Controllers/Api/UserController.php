<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // GET /api/users
    public function index(Request $request)
    {
        $q = User::query()->with('roles')->withoutTrashed(); // hide soft-deleted

        if ($s = trim((string)$request->get('search', ''))) {
            $q->where(function ($x) use ($s) {
                $x->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%");
            });
        }

        $perPage = (int)($request->get('perPage', 10)) ?: 10;
        $users   = $q->latest()->paginate($perPage);

        return response()->json([
            'data'  => UserResource::collection($users->items()),
            'total' => $users->total(),
        ]);
    }

    // GET /api/users/roles-all
    public function rolesAll()
    {
        return Role::query()
            ->select('id','name','guard_name')
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();
    }

    // POST /api/users
    // Accepts: name, username, password, image, and either role (string) OR roles[] (first item used)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            // 👇 optional improvement: ignore soft-deleted rows for uniqueness
            'username' => ['required','string','max:255', Rule::unique('users','username')->whereNull('deleted_at')],
            'password' => ['required', Password::min(6)],
            'role'     => ['nullable','string','exists:roles,name'],
            'image'    => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        // ✅ normalize role input (support role or roles[])
        $normalizedRole = $request->input('role');
        if ($normalizedRole === null) {
            $normalizedRole = data_get($request->input('roles'), '0'); // roles[0]
        }

        return DB::transaction(function () use ($request, $data, $normalizedRole) {
            $path = $request->hasFile('image')
                ? $request->file('image')->store('avatars', 'public')
                : null;

            $user = User::create([
                'name'     => $data['name'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'image'    => $path,
            ]);

            if (!empty($normalizedRole)) {
                $role = Role::findByName($normalizedRole, 'web');
                $user->syncRoles([$role]);
            }

            return response()->json(new UserResource($user->load('roles')), 201);
        });
    }

    // GET /api/users/{user}
    public function show(User $user)
    {
        return new UserResource($user->load('roles'));
    }

    // PUT/PATCH /api/users/{user}
    // Accepts name, username, (optional) password, (optional) image, and either role (string) OR roles[]
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'username' => ['required','string','max:255', Rule::unique('users','username')->ignore($user->id)],
            'password' => ['nullable', Password::min(6)],
            'role'     => ['nullable','string','sometimes','exists:roles,name'],
            'image'    => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        // ✅ normalize role input (support role or roles[])
        $normalizedRole = $request->input('role');
        if ($normalizedRole === null && $request->has('roles')) {
            $normalizedRole = data_get($request->input('roles'), '0'); // roles[0]
        }

        return DB::transaction(function () use ($request, $user, $data, $normalizedRole) {
            $imagePath = $user->image;
            if ($request->hasFile('image')) {
                $newPath = $request->file('image')->store('avatars', 'public');
                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $newPath;
            }

            $payload = [
                'name'     => $data['name'],
                'username' => $data['username'],
                'image'    => $imagePath,
            ];
            if (!empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);

            // ✅ role sync if provided (role or roles[])
            if ($request->has('role') || $request->has('roles')) {
                $roleName = trim((string)($normalizedRole ?? ''));
                if ($roleName === '') {
                    $user->syncRoles([]);
                } else {
                    $user->syncRoles([Role::findByName($roleName, 'web')]);
                }
            }

            return new UserResource($user->fresh()->load('roles'));
        });
    }

    // DELETE /api/users/{user}  -> HARD DELETE (DB row removed)
    public function destroy(User $user)
    {
        // optional cleanups
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
        $user->syncRoles([]);

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        // ✅ hard delete
        $user->forceDelete();

        return response()->json(['message' => 'Deleted']);
    }
}
