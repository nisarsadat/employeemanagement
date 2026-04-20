<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $perPage    = (int) $request->query('perPage', 25);
        $search     = trim((string) $request->query('search', ''));
        $provinceId = $request->query('province_id');

        $q = District::query()
            ->select('id', 'province_id', 'name', 'created_at', 'updated_at')
            ->with(['province:id,name'])
            ->when($provinceId, fn ($qq) => $qq->where('province_id', $provinceId))
            ->when($search !== '', function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhereHas('province', fn($p) => $p->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('name');

        return DistrictResource::collection($q->paginate($perPage));
    }

    public function show(District $district)
    {
        $district->load(['province:id,name']);
        return new DistrictResource($district);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'name'        => ['required', 'string', 'max:190', 'unique:districts,name'],
        ]);

        $district = District::create($data);
        $district->load(['province:id,name']);

        return (new DistrictResource($district))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, District $district)
    {
        $data = $request->validate([
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'name'        => ['required', 'string', 'max:190', 'unique:districts,name,' . $district->id],
        ]);

        $district->update($data);
        $district->load(['province:id,name']);

        return new DistrictResource($district);
    }

    public function destroy(District $district): JsonResponse
    {
        $district->delete();
        return response()->json(['message' => 'حذف شوو']);
    }
}