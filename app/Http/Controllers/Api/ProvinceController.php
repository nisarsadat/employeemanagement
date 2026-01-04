<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProvinceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 25);
        $search  = trim((string) $request->query('search', ''));

        $q = Province::query()
            ->withCount('districts')
            ->when($search !== '', function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name');

        return ProvinceResource::collection($q->paginate($perPage));
    }

    public function show(Province $province)
    {
        $province->loadCount('districts')->load('districts:id,province_id,name');
        return new ProvinceResource($province);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190', 'unique:provinces,name'],
        ]);

        $province = Province::create($data);
        $province->loadCount('districts');

        return (new ProvinceResource($province))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Province $province)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190', 'unique:provinces,name,' . $province->id],
        ]);

        $province->update($data);
        $province->loadCount('districts');

        return new ProvinceResource($province);
    }

    public function destroy(Province $province): JsonResponse
    {
        $province->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
