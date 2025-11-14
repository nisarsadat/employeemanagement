<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'username' => ['required','string','max:255','unique:users,username'],
            'password' => ['required', Password::min(6)],
            'image'    => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'role'     => ['nullable','string','exists:roles,name'], // optional
        ]);

        $path = $request->hasFile('image')
            ? $request->file('image')->store('avatars', 'public')
            : null;

        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'image'    => $path,
        ]);

        // Optional: make the very first user admin
        if (User::count() === 1) {
            $user->assignRole('admin');
        }

        // Optional: assign provided role (only if you allow this for the caller)
        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        $token = $user->createToken('em-app')->plainTextToken;

        return response()->json([
            'user'        => $user,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'token'       => $token,
            'token_type'  => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        /** @var \App\Models\User $user */
        $user  = $request->user();
        $token = $user->createToken('em-app')->plainTextToken;

        return response()->json([
            'user'        => $user,
            'roles'       => $user->getRoleNames(),                     // 👈 now included
            'permissions' => $user->getAllPermissions()->pluck('name'), // 👈 now included
            'token'       => $token,
            'token_type'  => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user'        => $user,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
