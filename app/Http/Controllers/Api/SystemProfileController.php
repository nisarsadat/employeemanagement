<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemProfileController extends Controller
{
    public function show()
    {
        return response()->json($this->payload(SystemProfile::query()->first()));
    }

    public function update(Request $request)
    {
        $request->merge([
            'admin_email' => $request->filled('admin_email') ? $request->input('admin_email') : null,
            'phone' => $request->filled('phone') ? $request->input('phone') : null,
            'address' => $request->filled('address') ? $request->input('address') : null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'default_language' => ['required', 'in:en,fa,ps'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:4096'],
        ]);

        $profile = SystemProfile::query()->first() ?? new SystemProfile();
        $profile->name = $data['name'];
        $profile->default_language = $data['default_language'];
        $profile->admin_email = $data['admin_email'] ?? null;
        $profile->phone = $data['phone'] ?? null;
        $profile->address = $data['address'] ?? null;

        if ($request->hasFile('logo')) {
            if ($profile->logo) {
                Storage::disk('public')->delete($profile->logo);
            }
            $profile->logo = $request->file('logo')->store('uploads/system', 'public');
        }

        $profile->save();

        return response()->json($this->payload($profile));
    }

    private function payload(?SystemProfile $profile): array
    {
        return [
            'name' => $profile?->name ?? '',
            'logo_url' => $profile?->logo ? asset('storage/' . ltrim($profile->logo, '/')) : null,
            'default_language' => $profile?->default_language ?: 'ps',
            'admin_email' => $profile?->admin_email ?? '',
            'phone' => $profile?->phone ?? '',
            'address' => $profile?->address ?? '',
        ];
    }
}
