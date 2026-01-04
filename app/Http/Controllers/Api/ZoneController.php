<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->perPage ?? 100);
        $search  = trim((string) ($request->search ?? ''));

        $query = Zone::query()
            ->select('id', 'name', 'created_at')
            ->withCount('hawzas')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        return response()->json([
            'data' => Zone::create($data),
        ], 201);
    }

    public function show(Zone $zone)
    {
        return response()->json([
            'data' => $zone->loadCount('hawzas'),
        ]);
    }

    public function update(Request $request, Zone $zone)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $zone->update($data);

        return response()->json([
            'data' => $zone->fresh()->loadCount('hawzas'),
        ]);
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();
        return response()->noContent();
    }
}
