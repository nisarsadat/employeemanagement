<?php

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
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', Password::min(6)],
            'image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'role'     => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $path = $request->hasFile('image')
            ? $request->file('image')->store('avatars', 'public')
            : null;

        $user = User::create([
            'name'                         => $data['name'],
            'username'                     => $data['username'],
            'password'                     => Hash::make($data['password']),
            'image'                        => $path,
            'is_online'                    => false,
            'last_seen_at'                 => null,
            'last_login_at'                => null,
            'last_logout_at'               => null,
            'current_session_started_at'   => null,
            'last_session_duration_seconds'=> 0,
            'total_login_duration_seconds' => 0,
            'today_login_duration_seconds' => 0,
            'today_login_duration_date'    => now()->toDateString(),
        ]);

        if (User::count() === 1) {
            $user->assignRole('admin');
        }

        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        $token = $user->createToken('em-app')->plainTextToken;

        return response()->json([
            'user'        => $user->load('roles'),
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
            return response()->json(['message' => 'پاسورډ یا کارن نوم مو غلط دی'], 422);
        }

        /** @var \App\Models\User $user */
        $user = User::where('username', $credentials['username'])->firstOrFail();

        $now = now();

        $todayStoredDate = $user->today_login_duration_date?->toDateString();
        $todaySeconds = (int) ($user->today_login_duration_seconds ?? 0);

        if ($todayStoredDate !== $now->toDateString()) {
            $todaySeconds = 0;
        }

        $user->update([
            'is_online' => true,
            'last_seen_at' => $now,
            'last_login_at' => $now,
            'current_session_started_at' => $now,
            'today_login_duration_seconds' => $todaySeconds,
            'today_login_duration_date' => $now->toDateString(),
        ]);

        $user = $user->fresh()->load('roles');

        $token = $user->createToken('em-app')->plainTextToken;

        return response()->json([
            'user'        => $user,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'token'       => $token,
            'token_type'  => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user) {
            $now = now();

            $todayStoredDate = $user->today_login_duration_date?->toDateString();
            $todaySeconds = (int) ($user->today_login_duration_seconds ?? 0);

            if ($todayStoredDate !== $now->toDateString()) {
                $todaySeconds = 0;
            }

            if (
                $user->is_online &&
                $user->current_session_started_at &&
                $user->current_session_started_at->toDateString() < $now->toDateString()
            ) {
                $user->current_session_started_at = $now->copy()->startOfDay();
            }

            $user->update([
                'last_seen_at' => $now,
                'today_login_duration_seconds' => $todaySeconds,
                'today_login_duration_date' => $now->toDateString(),
                'current_session_started_at' => $user->current_session_started_at,
            ]);

            $user = $user->fresh()->load('roles');
        }

        return response()->json([
            'user'        => $user,
            'roles'       => $user?->getRoleNames() ?? [],
            'permissions' => $user?->getAllPermissions()->pluck('name') ?? [],
        ]);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user) {
            $now = now();

            $sessionSeconds = 0;
            if ($user->current_session_started_at) {
                $sessionSeconds = max(0, $user->current_session_started_at->diffInSeconds($now));
            }

            $todayStoredDate = $user->today_login_duration_date?->toDateString();
            $todaySeconds = (int) ($user->today_login_duration_seconds ?? 0);

            if ($todayStoredDate !== $now->toDateString()) {
                $todaySeconds = 0;
            }

            if ($user->current_session_started_at) {
                if ($user->current_session_started_at->toDateString() === $now->toDateString()) {
                    $todaySeconds += $sessionSeconds;
                } elseif ($user->current_session_started_at->toDateString() < $now->toDateString()) {
                    $todaySeconds += $now->copy()->startOfDay()->diffInSeconds($now);
                }
            }

            $user->update([
                'is_online' => false,
                'last_seen_at' => $now,
                'last_logout_at' => $now,
                'last_session_duration_seconds' => $sessionSeconds,
                'total_login_duration_seconds' => (int) ($user->total_login_duration_seconds ?? 0) + $sessionSeconds,
                'today_login_duration_seconds' => $todaySeconds,
                'today_login_duration_date' => $now->toDateString(),
                'current_session_started_at' => null,
            ]);

            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
        }

        return response()->json(['message' => 'سیستم نه خارج شوی']);
    }
}