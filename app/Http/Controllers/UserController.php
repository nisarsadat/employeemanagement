<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Optionally protect with auth:sanctum middleware

    public function index()
    {
        $users = User::query()->latest()->paginate(15);
        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('picture') || $request->hasFile('image')) {
            $file = $request->file('picture') ?? $request->file('image');
            $data['photo_path'] = $file->store('users', 'public');
        }

        $user = User::create($data);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if ($request->hasFile('picture') || $request->hasFile('image')) {
            // delete old
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $file = $request->file('picture') ?? $request->file('image');
            $data['photo_path'] = $file->store('users', 'public');
        }

        $user->update($data);

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
            Storage::disk('public')->delete($user->photo_path);
        }

        $user->delete();

        return response()->noContent();
    }
}
