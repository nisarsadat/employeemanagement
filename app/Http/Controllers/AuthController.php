<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'username' => ['required','alpha_dash','max:60','unique:users,username'],
            'password' => ['required','string','min:8','confirmed'],
            'picture' => ['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:2048'],
            'active' => ['sometimes','boolean'],
        ]);

        if ($request->hasFile('picture')) {
            $data['photo_path'] = $request->file('picture')->store('users', 'public');
        }

        $user = User::create([
            'name' => $data['name'],
            'username'=> $data['username'],
            'password' => Hash::make($data['password']),
            'photo_path' => $data['photo_path'] ?? null,
            'active' => $data['active'] ?? true,
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $creds = $request->validate([
            'username' => ['required','string'],
            'password' => ['required','string'],
        ]);

        $user = User::where('username', $creds['username'])->first();

        if (! $user || ! Hash::check($creds['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->active) {
            throw ValidationException::withMessages([
                'username' => ['User is inactive.'],
            ]);
        }

        // Create Sanctum token
        $tokenName = 'api-'.Str::uuid();
        $token = $user->createToken($tokenName)->plainTextToken;

        // Include roles and permissions
        $roles = $user->getRoleNames();
        $permissions = $user->getPermissionNames();

        return response()->json([
            'token' => $token,
            'roles' => $roles,
            'permissions' => $permissions,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'photo_url' => $user->photo_url,
                'active' => (bool) $user->active,
                'roles' => $roles,
                'permissions' => $permissions,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $roles = $user->getRoleNames();
        $permissions = $user->getPermissionNames();
        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'photo_url' => $user->photo_url,
                'active' => (bool) $user->active,
                'roles' => $roles,
                'permissions' => $permissions,
            ],
        ]);
    }
}
