<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreHeistRequest;
use App\Http\Requests\fifthyFormats\UpdateHeistRequest;
use App\Http\Resources\fifthyFormats\HeistResource;
use App\Models\fifthyFormats\Heist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeistController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');

        $q = Heist::query()->with('resource')->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('gun_type', 'like', "%{$search}%")
                   ->orWhere('note', 'like', "%{$search}%");
            });
        }

        return HeistResource::collection($q->paginate($perPage));
    }

    public function show(Heist $heist)
    {
        $heist->load('resource');
        return new HeistResource($heist);
    }

    public function store(StoreHeistRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/heists', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $heist = Heist::create($data);
        $heist->load('resource');

        return (new HeistResource($heist))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateHeistRequest $request, Heist $heist)
    {
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            if ($heist->picture && str_starts_with($heist->picture, 'storage/')) {
                $diskPath = str_replace('storage/', '', $heist->picture);
                Storage::disk('public')->delete($diskPath);
            }
            $path = $request->file('picture')->store('uploads/heists', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            if (!array_key_exists('picture', $data) || !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $heist->update($data);
        $heist->load('resource');

        return new HeistResource($heist);
    }

    public function destroy(Heist $heist)
    {
        if ($heist->picture && str_starts_with($heist->picture, 'storage/')) {
            $diskPath = str_replace('storage/', '', $heist->picture);
            Storage::disk('public')->delete($diskPath);
        }

        $heist->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
