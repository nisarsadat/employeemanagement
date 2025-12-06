<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreLostMujremeenRequest;
use App\Http\Requests\fifthyformatone\UpdateLostMujremeenRequest;
use App\Http\Resources\fifthyformatone\LostMujremeenResource;
use App\Models\fifthyformatone\LostMujremeen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LostMujremeenController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');

        $q = LostMujremeen::query()
            ->with('resource')
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%")
                   ->orWhere('jorm', 'like', "%{$search}%");
            });
        }

        return LostMujremeenResource::collection($q->paginate($perPage));
    }

    public function show(LostMujremeen $lostMujremeen)
    {
        $lostMujremeen->load('resource');
        return new LostMujremeenResource($lostMujremeen);
    }

    public function store(StoreLostMujremeenRequest $request)
    {
        $data = $request->validated();

        // 📷 handle picture upload
        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/lost-mujremeens', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // if picture key exists but isn't a string, drop it
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $item = LostMujremeen::create($data);
        $item->load('resource');

        return (new LostMujremeenResource($item))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateLostMujremeenRequest $request, LostMujremeen $lostMujremeen)
    {
        $data = $request->validated();

        // 📷 handle picture update
        if ($request->hasFile('picture')) {
            // delete old file if exists
            if ($lostMujremeen->picture && str_starts_with($lostMujremeen->picture, 'storage/')) {
                $diskPath = str_replace('storage/', '', $lostMujremeen->picture);
                Storage::disk('public')->delete($diskPath);
            }

            $path = $request->file('picture')->store('uploads/lost-mujremeens', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // if picture not in request OR not a string -> don't override
            if (!array_key_exists('picture', $data) || !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $lostMujremeen->update($data);
        $lostMujremeen->load('resource');

        return new LostMujremeenResource($lostMujremeen);
    }

    public function destroy(LostMujremeen $lostMujremeen)
    {
        // delete picture file if stored
        if ($lostMujremeen->picture && str_starts_with($lostMujremeen->picture, 'storage/')) {
            $diskPath = str_replace('storage/', '', $lostMujremeen->picture);
            Storage::disk('public')->delete($diskPath);
        }

        $lostMujremeen->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        // optional: also clean up pictures for each
        $items = LostMujremeen::whereIn('id', $ids)->get();
        foreach ($items as $item) {
            if ($item->picture && str_starts_with($item->picture, 'storage/')) {
                $diskPath = str_replace('storage/', '', $item->picture);
                Storage::disk('public')->delete($diskPath);
            }
        }

        LostMujremeen::destroy($ids);

        return response()->noContent();
    }
}
