<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\ZoneEmployee;
use App\Http\Requests\ZoneEmployeeRequest;
use App\Http\Resources\ZoneEmployeeResource;

class ZoneEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $items = ZoneEmployee::query()
            ->search($search)
            ->latest()
            ->paginate($perPage);

        return ZoneEmployeeResource::collection($items);
    }

    public function store(ZoneEmployeeRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/zone-employees', 'public');
            $data['picture'] = "storage/{$path}";
        }

        $item = ZoneEmployee::create($data);
        return ZoneEmployeeResource::make($item);
    }

    public function show(ZoneEmployee $zoneEmployee)
    {
        return ZoneEmployeeResource::make($zoneEmployee);
    }

    public function update(ZoneEmployeeRequest $request, ZoneEmployee $zoneEmployee)
    {
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            if ($zoneEmployee->picture && str_starts_with($zoneEmployee->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $zoneEmployee->picture));
            }
            $path = $request->file('picture')->store('uploads/zone-employees', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            unset($data['picture']);
        }

        $zoneEmployee->update($data);
        return ZoneEmployeeResource::make($zoneEmployee);
    }

    public function destroy(ZoneEmployee $zoneEmployee)
    {
        if ($zoneEmployee->picture && str_starts_with($zoneEmployee->picture, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $zoneEmployee->picture));
        }
        $zoneEmployee->delete();
        return response()->noContent();
    }

    // optional
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('zoneEmployeeIds', []);
        ZoneEmployee::destroy($ids);
        return response()->noContent();
    }
}
