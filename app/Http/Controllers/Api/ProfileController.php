<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // GET /api/profile
    public function show(Request $request)
    {
        return new UserResource($request->user()->load('roles'));
    }

    // PUT /api/profile
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id)->whereNull('deleted_at'),
            ],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        return DB::transaction(function () use ($request, $user, $data) {
            $imagePath = $user->image;

            if ($request->hasFile('image')) {
                $newPath = $request->file('image')->store('avatars', 'public');

                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }

                $imagePath = $newPath;
            }

            $user->update([
                'name' => $data['name'],
                'username' => $data['username'],
                'image' => $imagePath,
            ]);

            return response()->json([
                'message' => 'تغییر شو',
                'data' => new UserResource($user->fresh()->load('roles')),
            ]);
        });
    }

    // PUT /api/profile/change-password
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', Password::min(6), 'different:current_password'],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'اوسنۍ پاسورډ مو غلط دی',
                'errors' => [
                    'current_password' => ['اوسنۍ پاسورډ مو غلط دی'],
                ],
            ], 422);
        }

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'password' => Hash::make($data['new_password']),
            ]);

            // revoke all Sanctum tokens so user must login again
            $user->tokens()->delete();
        });

        return response()->json([
            'message' => 'پاسورډ  بدل شو مهرباني وکوی له سره سیستم ته داخل شئ',
            'force_logout' => true,
        ]);
    }
}