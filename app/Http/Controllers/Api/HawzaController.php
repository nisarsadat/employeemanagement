<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hawza;
use Illuminate\Http\Request;

class HawzaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->perPage ?? 100);
        $search  = trim((string) ($request->search ?? ''));
        $zoneId  = $request->zone_id;

        $query = Hawza::query()
            ->select('id', 'zone_id', 'name', 'created_at')
            ->with(['zone:id,name'])
            ->orderByDesc('id');

        if (!empty($zoneId)) {
            $query->where('zone_id', $zoneId);
        }

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
            'name'    => ['required', 'string', 'max:255'],
        ]);

        $hawza = Hawza::create($data);

        return response()->json([
            'data' => $hawza->load(['zone:id,name']),
        ], 201);
    }

    public function show(Hawza $hawza)
    {
        return response()->json([
            'data' => $hawza->load(['zone:id,name']),
        ]);
    }

    public function update(Request $request, Hawza $hawza)
    {
        $data = $request->validate([
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
            'name'    => ['required', 'string', 'max:255'],
        ]);

        $hawza->update($data);

        return response()->json([
            'data' => $hawza->fresh()->load(['zone:id,name']),
        ]);
    }

    public function destroy(Hawza $hawza)
    {
        $hawza->delete();
        return response()->noContent();
    }
}
